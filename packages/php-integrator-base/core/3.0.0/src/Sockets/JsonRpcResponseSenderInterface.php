<?php

namespace PhpIntegrator\Sockets;

/**
 * Interface for classes that can send {@see JsonRpcResponse} objects (to a stream, socket, file, ...).
 */
interface JsonRpcResponseSenderInterface
{
    /**
     * @param JsonRpcResponse $response
     * @param bool            $force    Whether to force sending the response now. This can be useful when the sender
     *                                  is part of a buffering or scheduling system to avoid delaying the send until
     *                                  the next buffer full, timeout, tick, ...
     *
     * @return void
     */
    public function send(JsonRpcResponse $response, bool $force = false): void;
}
