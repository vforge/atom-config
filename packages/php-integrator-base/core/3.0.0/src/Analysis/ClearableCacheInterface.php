<?php

namespace PhpIntegrator\Analysis;

/**
 * Inerface for classes that perform caching and have the ability to clear that cache.
 */
interface ClearableCacheInterface
{
    /**
     * @return void
     */
    public function clearCache(): void;
}
