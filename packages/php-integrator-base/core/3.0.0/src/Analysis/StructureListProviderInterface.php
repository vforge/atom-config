<?php

namespace PhpIntegrator\Analysis;

use RuntimeException;

/**
 * Retrieves a list of structures.
 */
interface StructureListProviderInterface
{
     /**
      * @throws RuntimeException
      *
      * @return array[]
      */
     public function getAll(): array;
}
