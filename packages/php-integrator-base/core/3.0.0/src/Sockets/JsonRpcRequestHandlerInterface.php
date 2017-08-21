<?php

namespace PhpIntegrator\Sockets;

/**
 * Interface for {@see JsonRpcRequest} handlers.
 */
interface JsonRpcRequestHandlerInterface
{
    /**
     * @param JsonRpcRequest                      $request
     * @param JsonRpcResponseSenderInterface|null $jsonRpcResponseSender If the caller supports it, he can provide this
     *                                                                   object to allow the handler to manually send
     *                                                                   intermediate responses related to the request
     *                                                                   (i.e. if it wants to stream progress
     *                                                                   information). The final result of the operation
     *                                                                   must still be returned after the operation
     *                                                                   completes.
     *
     * @return JsonRpcResponse
     */
    public function handle(
        JsonRpcRequest $request,
        ?JsonRpcResponseSenderInterface $jsonRpcResponseSender = null
    ): JsonRpcResponse;
}
