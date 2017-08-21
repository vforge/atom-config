<?php

namespace PhpIntegrator\UserInterface;

use Ds;
use React;
use Throwable;
use ArrayObject;
use RuntimeException;
use UnexpectedValueException;

use PhpIntegrator\Indexing\Indexer;
use PhpIntegrator\Indexing\IncorrectDatabaseVersionException;

use PhpIntegrator\Sockets\JsonRpcError;
use PhpIntegrator\Sockets\SocketServer;
use PhpIntegrator\Sockets\JsonRpcRequest;
use PhpIntegrator\Sockets\JsonRpcResponse;
use PhpIntegrator\Sockets\JsonRpcErrorCode;
use PhpIntegrator\Sockets\RequestParsingException;
use PhpIntegrator\Sockets\JsonRpcRequestHandlerInterface;
use PhpIntegrator\Sockets\JsonRpcResponseSenderInterface;
use PhpIntegrator\Sockets\JsonRpcConnectionHandlerFactory;

use React\EventLoop\LoopInterface;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Application extension that can handle JSON-RPC requests.
 */
class JsonRpcApplication extends AbstractApplication implements JsonRpcRequestHandlerInterface
{
    /**
     * A stream that is used to read and write STDIN data from.
     *
     * As there is no actual STDIN when working with sockets, this temporary stream is used to transparently replace
     * it with another stream.
     *
     * @var resource|null
     */
    private $stdinStream;

    /**
     * @inheritDoc
     */
    public function run()
    {
        $options = getopt('p:', [
            'port:'
        ]);

        $requestHandlingPort = $this->getRequestHandlingPortFromOptions($options);

        $this->stdinStream = fopen('php://memory', 'w+');

        /** @var LoopInterface $loop */
        $loop = React\EventLoop\Factory::create();

        try {
            $this->setupRequestHandlingSocketServer($loop, $requestHandlingPort);
        } catch (RuntimeException $e) {
            fwrite(STDERR, 'Socket already in use!');
            fclose($this->stdinStream);
            return 2;
        }

        echo "Starting socket server on port {$requestHandlingPort}...\n";

        $this->instantiateRequiredServices($this->getContainer());

        $loop->run();

        fclose($this->stdinStream);

        return 0;
    }

    /**
     * @param array $options
     *
     * @throws UnexpectedValueException
     *
     * @return int
     */
    protected function getRequestHandlingPortFromOptions(array $options): int
    {
        if (isset($options['p'])) {
            return (int) $options['p'];
        } elseif (isset($options['port'])) {
            return (int) $options['port'];
        }

        throw new UnexpectedValueException('A socket port for handling requests must be specified');
    }

    /**
     * @param React\EventLoop\LoopInterface $loop
     * @param int                           $port
     *
     * @throws RuntimeException
     *
     * @return void
     */
    protected function setupRequestHandlingSocketServer(React\EventLoop\LoopInterface $loop, int $port): void
    {
        $connectionHandlerFactory = new JsonRpcConnectionHandlerFactory($this);

        $requestHandlingSocketServer = new SocketServer($port, $loop, $connectionHandlerFactory);
    }

    /**
     * @inheritDoc
     */
    public function handle(
        JsonRpcRequest $request,
        JsonRpcResponseSenderInterface $jsonRpcResponseSender = null
    ): JsonRpcResponse {
        $error = null;
        $result = null;

        try {
            $result = $this->handleRequest($request, $jsonRpcResponseSender);
        } catch (RequestParsingException $e) {
            $error = new JsonRpcError(JsonRpcErrorCode::INVALID_PARAMS, $e->getMessage());
        } catch (Command\InvalidArgumentsException $e) {
            $error = new JsonRpcError(JsonRpcErrorCode::INVALID_PARAMS, $e->getMessage());
        } catch (IncorrectDatabaseVersionException $e) {
            $error = new JsonRpcError(JsonRpcErrorCode::DATABASE_VERSION_MISMATCH, $e->getMessage());
        } catch (\RuntimeException $e) {
            $error = new JsonRpcError(JsonRpcErrorCode::GENERIC_RUNTIME_ERROR, $e->getMessage());
        } catch (\Throwable $e) {
            $error = new JsonRpcError(JsonRpcErrorCode::FATAL_SERVER_ERROR, $e->getMessage(), [
                'line'      => $e->getLine(),
                'file'      => $e->getFile(),
                'backtrace' => $this->getCompleteBacktraceFromThrowable($e)
            ]);
        }

        return new JsonRpcResponse($request->getId(), $result, $error);
    }

    /**
     * @param Throwable $throwable
     *
     * @return string
     */
    protected function getCompleteBacktraceFromThrowable(Throwable $throwable): string
    {
        $counter = 1;

        $reducer = function (string $carry, Throwable $item) use (&$counter): string {
            if (!empty($carry)) {
                $carry .= "\n \n";
            }

            $carry .= "→ Message {$counter}\n";
            $carry .= $item->getMessage() . "\n \n";

            $carry .= "→ Location {$counter}\n";
            $carry .= $item->getFile() . ':' . $item->getLine() . "\n \n";

            $carry .= "→ Backtrace {$counter}\n";
            $carry .= $item->getTraceAsString();

            ++$counter;

            return $carry;
        };

        return $this->getThrowableVector($throwable)->reduce($reducer, '');
    }

    /**
     * @param Throwable $throwable
     *
     * @return Ds\Vector
     */
    protected function getThrowableVector(Throwable $throwable): Ds\Vector
    {
        $vector = new Ds\Vector();

        $item = $throwable;

        while ($item) {
            $vector[] = $item;

            $item = $item->getPrevious();
        }

        return $vector;
    }

    /**
     * @param JsonRpcRequest                      $request
     * @param JsonRpcResponseSenderInterface|null $jsonRpcResponseSender
     *
     * @return mixed
     */
    protected function handleRequest(
        JsonRpcRequest $request,
        ?JsonRpcResponseSenderInterface $jsonRpcResponseSender = null
    ) {
        $params = $request->getParams();

        $this->configureProgressStreamingCallback($request, $jsonRpcResponseSender);

        if (isset($params['stdinData'])) {
            ftruncate($this->stdinStream, 0);
            fwrite($this->stdinStream, $params['stdinData']);
            rewind($this->stdinStream);
        }

        if (isset($params['database'])) {
            $this->setDatabaseFile($params['database']);
        }

        unset($params['stdinData'], $params['database']);

        $command = $this->getCommandByMethod($request->getMethod());

        $result = $command->execute(new ArrayObject($params));

        return $result;
    }

    /**
     * @param string $method
     *
     * @return Command\CommandInterface
     */
    protected function getCommandByMethod(string $method): Command\CommandInterface
    {
        try {
            return $this->getContainer()->get($method . 'Command');
        } catch (ServiceNotFoundException $e) {
            throw new RequestParsingException('Method "' . $method . '" was not found');
        }

        return null; // Never reached.
    }

    /**
     * @param JsonRpcRequest                      $request
     * @param JsonRpcResponseSenderInterface|null $jsonRpcResponseSender
     *
     * @return void
     */
    protected function configureProgressStreamingCallback(
        JsonRpcRequest $request,
        JsonRpcResponseSenderInterface $jsonRpcResponseSender = null
    ): void {
        $progressStreamingCallback = null;

        if ($jsonRpcResponseSender) {
            $progressStreamingCallback = $this->createProgressStreamingCallback($request, $jsonRpcResponseSender);
        }

        /** @var Indexer $indexer */
        $indexer = $this->getContainer()->get('indexer');
        $indexer->setProgressStreamingCallback($progressStreamingCallback);
    }

    /**
     * @inheritDoc
     */
    public function getStdinStream()
    {
        return $this->stdinStream;
    }

    /**
     * @param JsonRpcRequest                 $request
     * @param JsonRpcResponseSenderInterface $jsonRpcResponseSender
     *
     * @return \Closure
     */
    public function createProgressStreamingCallback(
        JsonRpcRequest $request,
        JsonRpcResponseSenderInterface $jsonRpcResponseSender
    ): \Closure {
        return function ($progress) use ($request, $jsonRpcResponseSender) {
            $jsonRpcResponse = new JsonRpcResponse(null, [
                'type'      => 'reindexProgressInformation',
                'requestId' => $request->getId(),
                'progress'  => $progress
            ]);

            // We may well be sending data to the connection as needed, but during this process we never end up back in
            // the main loop, thus the writes are never actually performed by the React event loop. For this reason
            // we force the write.
            $jsonRpcResponseSender->send($jsonRpcResponse, true);
        };
    }
}
