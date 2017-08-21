<?php

namespace PhpIntegrator\Sockets;

use React\Socket\Connection;

/**
 * Factory that creates instances of {@see JsonRpcConnectionHandler}.
 */
class JsonRpcConnectionHandlerFactory implements ConnectionHandlerFactoryInterface
{
    /**
     * @var JsonRpcRequestHandlerInterface
     */
    private $jsonRpcRequestHandler;

    /**
     * @param JsonRpcRequestHandlerInterface $jsonRpcRequestHandler
     */
    public function __construct(JsonRpcRequestHandlerInterface $jsonRpcRequestHandler)
    {
        $this->jsonRpcRequestHandler = $jsonRpcRequestHandler;
    }

    /**
     * @inheritDoc
     */
    public function create(Connection $connection)
    {
        return new JsonRpcConnectionHandler($connection, $this->jsonRpcRequestHandler);
    }
}
