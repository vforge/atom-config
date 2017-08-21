<?php

namespace PhpIntegrator\Sockets;

use RuntimeException;
use SplObjectStorage;

use React\EventLoop\LoopInterface;

use React\Socket\Server;
use React\Socket\Connection;

/**
 * Represents a socket server that handles communication with the core.
 *
 * This class simply requests a configured factory to create a handler for each new connection and does not handle any
 * communication itself.
 */
class SocketServer
{
    /**
     * @var Server
     */
    private $server;

    /**
     * @var SplObjectStorage
     */
    private $connectionMap;

    /**
     * @var ConnectionHandlerFactoryInterface
     */
    private $connectionHandlerFactory;

    /**
     * @param int                               $port
     * @param LoopInterface                     $loop
     * @param ConnectionHandlerFactoryInterface $connectionHandlerFactory
     *
     * @throws RuntimeException when setting up the server is impossible (e.g. the socket is already in use).
     */
    public function __construct(int $port, LoopInterface $loop, ConnectionHandlerFactoryInterface $connectionHandlerFactory)
    {
        $this->server = new Server($port, $loop);

        $this->connectionMap = new SplObjectStorage();
        $this->connectionHandlerFactory = $connectionHandlerFactory;

        $this->server->on('connection', function (Connection $connection) {
             $this->onConnectionEstablished($connection);
        });
    }

    /**
     * @param Connection $connection
     *
     * @return void
     */
    protected function onConnectionEstablished(Connection $connection): void
    {
        $handler = $this->connectionHandlerFactory->create($connection);

        $this->connectionMap->attach($connection, $handler);

        $connection->on('close', function () use ($connection) {
            $this->onConnectionClosed($connection);
        });
    }

    /**
     * @param Connection $connection
     *
     * @return void
     */
    protected function onConnectionClosed(Connection $connection): void
    {
        $this->connectionMap->detach($connection);
    }
}
