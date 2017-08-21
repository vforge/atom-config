<?php

namespace PhpIntegrator\Sockets;

use React\Socket\Connection;

/**
 * Factory that creates instances of a class that can handle a {@see Connection}.
 */
interface ConnectionHandlerFactoryInterface
{
    /**
     * @param Connection $connection
     *
     * @return object
     */
    public function create(Connection $connection);
}
