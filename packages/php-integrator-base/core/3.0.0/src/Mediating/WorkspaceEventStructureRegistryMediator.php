<?php

namespace PhpIntegrator\Mediating;

use Evenement\EventEmitterInterface;

use PhpIntegrator\Analysis\StructureListRegistry;

use PhpIntegrator\Indexing\WorkspaceEventName;

/**
 * Mediator that updates the structure registry when workspace events happen.
 */
class WorkspaceEventStructureRegistryMediator
{
    /**
     * @var StructureListRegistry
     */
    private $structureListRegistry;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @param StructureListRegistry  $structureListRegistry
     * @param EventEmitterInterface $eventEmitter
     */
    public function __construct(
        StructureListRegistry $structureListRegistry,
        EventEmitterInterface $eventEmitter
    ) {
        $this->structureListRegistry = $structureListRegistry;
        $this->eventEmitter = $eventEmitter;

        $this->setup();
    }

    /**
     * @return void
     */
    protected function setup(): void
    {
        $this->eventEmitter->on(WorkspaceEventName::CHANGED, function (string $filePath) {
            $this->structureListRegistry->reset();
        });
    }
}
