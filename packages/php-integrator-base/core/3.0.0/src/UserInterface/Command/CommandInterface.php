<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;

/**
 * Interface for commands.
 */
interface CommandInterface
{
    /**
     * Executes the command.
     *
     * @param ArrayAccess $arguments
     *
     * @throws InvalidArgumentsException
     *
     * @return mixed
     */
    public function execute(ArrayAccess $arguments);
}
