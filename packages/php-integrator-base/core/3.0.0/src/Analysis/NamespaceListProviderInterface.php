<?php

namespace PhpIntegrator\Analysis;

use RuntimeException;

/**
 * Interface for classes that retrieve a list of namespaces.
 */
interface NamespaceListProviderInterface
{
     /**
      * @throws RuntimeException
      *
      * @return array[]
      */
     public function getAll(): array;
}
