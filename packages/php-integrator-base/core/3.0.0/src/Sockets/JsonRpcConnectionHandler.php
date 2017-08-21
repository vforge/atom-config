<?php

namespace PhpIntegrator\Sockets;

use UnexpectedValueException;

use React\Socket\Connection;

/**
 * Handles socket connections that send JSON-RPC requests via a simple HTTP-like protocol and dispatches the requests
 * to a handler.
 */
class JsonRpcConnectionHandler implements JsonRpcResponseSenderInterface
{
    /**
     * @var string
     */
    protected const HEADER_DELIMITER = "\r\n";

    /**
     * @var array
     */
    private $request;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var JsonRpcRequestHandlerInterface
     */
    private $jsonRpcRequestHandler;

    /**
     * @param Connection                     $connection
     * @param JsonRpcRequestHandlerInterface $jsonRpcRequestHandler
     */
    public function __construct(Connection $connection, JsonRpcRequestHandlerInterface $jsonRpcRequestHandler)
    {
        $this->connection = $connection;
        $this->jsonRpcRequestHandler = $jsonRpcRequestHandler;

        $this->setup();
    }

    /**
     * @return void
     */
    protected function setup(): void
    {
        $this->resetRequestState();

        $this->connection->on('data', [$this, 'onDataReceived']);
        $this->connection->on('end', [$this, 'onEnded']);
        $this->connection->on('close', [$this, 'onClosed']);
    }

    /**
     * @return void
     */
    protected function resetRequestState(): void
    {
        $this->request = [
            'length'           => null,
            'mimeType'         => null,
            'wasBoundaryFound' => false,
            'bytesRead'        => 0,
            'content'          => ''
        ];
    }

    /**
     * @param string $data
     *
     * @return void
     */
    public function onDataReceived(string $data): void
    {
        try {
            $this->processData($data);
        } catch (RequestParsingException $e) {
            $this->handleRequestParsingException($e);
        }
    }

    /**
     * @return void
     */
    public function onEnded(): void
    {

    }

    /**
     * @return void
     */
    public function onClosed(): void
    {

    }

    /**
     * @param RequestParsingException $e
     *
     * @return void
     */
    protected function handleRequestParsingException(RequestParsingException $e): void
    {
        $this->resetRequestState();
    }

    /**
     * @param string $data
     *
     * @return void
     */
    protected function processData(string $data): void
    {
        $bytesRead = 0;

        if ($this->request['length'] === null) {
            $contentLengthHeader = $this->readRawHeader($data);
            $contentLength = $this->getLengthFromContentLengthHeader($contentLengthHeader);

            $this->request['length'] = $contentLength;

            $bytesRead = strlen($contentLengthHeader) + strlen(self::HEADER_DELIMITER);
        } elseif (!$this->request['wasBoundaryFound']) {
            $header = $this->readRawHeader($data);

            if (empty($header)) {
                $this->request['wasBoundaryFound'] = true;
            }

            $bytesRead = strlen($header) + strlen(self::HEADER_DELIMITER);
        } else {
            $bytesRead = min(strlen($data), $this->request['length'] - $this->request['bytesRead']);

            $this->request['content'] .= substr($data, 0, $bytesRead);
            $this->request['bytesRead'] += $bytesRead;

            if ($this->request['bytesRead'] == $this->request['length']) {
                $jsonRpcRequest = null;

                try {
                    $jsonRpcRequest = $this->getJsonRpcRequestFromRequestContent($this->request['content']);
                } catch (UnexpectedValueException $e) {
                    $jsonRpcRequest = null;
                }

                if ($jsonRpcRequest !== null) {
                    $jsonRpcResponse = $this->getJsonRpcResponseForJsonRpcRequest($jsonRpcRequest);

                    $this->send($jsonRpcResponse);
                } else {
                    trigger_error(
                        'The request body was not valid JSON. Its content was "' . $this->request['content'] . '"',
                        E_USER_WARNING
                    );
                }

                $this->resetRequestState();
            }
        }

        $data = substr($data, $bytesRead);

        if (strlen($data) > 0) {
            $this->processData($data);
        }
    }

    /**
     * @param JsonRpcRequest $request
     *
     * @return JsonRpcResponse
     */
    protected function getJsonRpcResponseForJsonRpcRequest(JsonRpcRequest $request): JsonRpcResponse
    {
        return $this->jsonRpcRequestHandler->handle($request, $this);
    }

    /**
     * @inheritDoc
     */
    public function send(JsonRpcResponse $response, bool $force = false): void
    {
        $responseContent = $this->getEncodedResponse($response);

        if (empty($responseContent)) {
            trigger_error(
                'Empty JSON body encountered after encoding, JSON reports "' . json_last_error_msg() . '"',
                E_USER_WARNING
            );
        }

        $this->writeRawResponse($responseContent, $force);
    }

    /**
     * @param JsonRpcResponse $response
     *
     * @return string
     */
    protected function getEncodedResponse(JsonRpcResponse $response): string
    {
        $data = json_encode($response);

        // See also #147 and #248.
        if (json_last_error() === JSON_ERROR_UTF8) {
            trigger_error(
                'The response could not be encoded in UTF-8 properly. Attempting to recover.',
                E_USER_WARNING
            );

            $serializedData = $response->jsonSerialize();
            $serializedData = $this->getCorrectedUtf8Data($serializedData);

            $data = json_encode($serializedData);
        }

        return $data;
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    protected function getCorrectedUtf8Data($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->getCorrectedUtf8Data($value);
            }
        } elseif (is_string($data)) {
            return utf8_encode($data);
        }

        return $data;
    }

    /**
     * @param string $content
     *
     * @return JsonRpcRequest
     */
    protected function getJsonRpcRequestFromRequestContent(string $content): JsonRpcRequest
    {
        return JsonRpcRequest::createFromJson($this->request['content']);
    }

    /**
     * @param string $data
     *
     * @throws RequestParsingException
     *
     * @return string
     */
    protected function readRawHeader(string $data): string
    {
        $end = strpos($data, self::HEADER_DELIMITER);

        if ($end === -1) {
            throw new RequestParsingException('Header delimiter not found');
        }

        return substr($data, 0, $end);
    }

    /**
     * @param string $content
     * @param bool   $force
     *
     * @return void
     */
    protected function writeRawResponse(string $content, bool $force = false): void
    {
        $this->connection->write('Content-Length: ' . strlen($content) . self::HEADER_DELIMITER);
        $this->connection->write(self::HEADER_DELIMITER);
        $this->connection->write($content);

        if ($force) {
            // The data we write to the socket is internally buffered until the next event loop tick. If the caller
            // is attempting to stream data (i.e. he is in a loop itself), the data will never be written until that
            // loop exits. This way we can force the buffer to be drained now.
            $this->connection->getBuffer()->handleWrite();
        }
    }

    /**
     * @param string $rawHeader
     *
     * @throws RequestParsingException
     *
     * @return int
     */
    protected function getLengthFromContentLengthHeader(string $rawHeader): int
    {
        $parts = explode(':', $rawHeader, 2);

        if (count($parts) !== 2) {
            throw new RequestParsingException('Invalid header format encountered');
        }

        list($headerName, $contentLength) = $parts;

        $contentLength = trim($contentLength);

        if (!$contentLength || !is_numeric($contentLength)) {
            throw new RequestParsingException('Content of the Content-Length header is not a valid number');
        }

        return $contentLength;
    }
}
