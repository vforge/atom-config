<?php

namespace PhpIntegrator\Analysis;

/**
 * Delegates classlike existence checking to another object and adds a caching wrapper.
 */
class ArrayCachingClasslikeExistenceChecker implements ClasslikeExistenceCheckerInterface, ClearableCacheInterface
{
    /**
     * @var ClasslikeExistenceCheckerInterface
     */
    private $delegate;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @param ClasslikeExistenceCheckerInterface $delegate
     */
    public function __construct(ClasslikeExistenceCheckerInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * @inheritDoc
     */
    public function doesClassExist(string $fqcn): bool
    {
        if (!isset($this->cache[$fqcn])) {
            $this->cache[$fqcn] = $this->delegate->doesClassExist($fqcn);
        }

        return $this->cache[$fqcn];
    }

    /**
     * @inheritDoc
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}
