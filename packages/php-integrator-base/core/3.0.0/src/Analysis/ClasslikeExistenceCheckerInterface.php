<?php

namespace PhpIntegrator\Analysis;

/**
 * Inerface for classes that can check if a classlike exists.
 */
interface ClasslikeExistenceCheckerInterface
{
    /**
     * @param string $fqcn
     *
     * @return bool
     */
    public function doesClassExist(string $fqcn): bool;
}
