<?php

namespace PhpIntegrator\Analysis;

use PhpIntegrator\Indexing\Structures;

interface MetadataProviderInterface
{
    /**
     * @param string $fqcn
     * @param string $method
     *
     * @return Structures\MetaStaticMethodType[]
     */
    public function getMetaStaticMethodTypesFor(string $fqcn, string $method): array;
}
