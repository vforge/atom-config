<?php

namespace PhpIntegrator\Analysis;

use PhpIntegrator\Indexing\Structures;
use PhpIntegrator\Indexing\ManagerRegistry;

/**
 * Checks if a classlike exists via Doctrine.
 */
class DoctrineClasslikeExistenceChecker implements ClasslikeExistenceCheckerInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function doesClassExist(string $fqcn): bool
    {
        return !!$this->managerRegistry->getRepository(Structures\Structure::class)->findOneBy([
            'fqcn' => $fqcn
        ]);
    }
}
