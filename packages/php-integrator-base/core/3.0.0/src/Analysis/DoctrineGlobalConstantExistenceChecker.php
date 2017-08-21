<?php

namespace PhpIntegrator\Analysis;

use PhpIntegrator\Indexing\Structures;
use PhpIntegrator\Indexing\ManagerRegistry;

use PhpIntegrator\NameQualificationUtilities\ConstantPresenceIndicatorInterface;

/**
 * Checks if a constant exists via Doctrine.
 */
class DoctrineGlobalConstantExistenceChecker implements ConstantPresenceIndicatorInterface
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
    public function isPresent(string $fullyQualifiedName): bool
    {
        return !!$this->managerRegistry->getRepository(Structures\Constant::class)->findOneBy([
            'fqcn' => $fullyQualifiedName
        ]);
    }
}
