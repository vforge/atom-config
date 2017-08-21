<?php

namespace PhpIntegrator\Analysis;

use RuntimeException;

/**
 * Retrieves a list of (global) functions.
 */
interface FunctionListProviderInterface
{
     /**
      * @throws RuntimeException
      *
      * @return array[]
      */
     public function getAll(): array;
}
