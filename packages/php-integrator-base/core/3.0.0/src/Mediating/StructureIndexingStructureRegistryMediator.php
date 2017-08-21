<?php

namespace PhpIntegrator\Mediating;

use Evenement\EventEmitterInterface;

use PhpIntegrator\Analysis\StructureListRegistry;

use PhpIntegrator\Analysis\Conversion\ClasslikeConverter;

use PhpIntegrator\Indexing\Structures;
use PhpIntegrator\Indexing\IndexingEventName;

/**
 * Mediator that updates the structure registry when structure indexing events happen.
 */
class StructureIndexingStructureRegistryMediator
{
    /**
     * @var StructureListRegistry
     */
    private $structureListRegistry;

    /**
     * @var ClasslikeConverter
     */
    private $classlikeConverter;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @param StructureListRegistry $structureListRegistry
     * @param ClasslikeConverter    $classlikeConverter
     * @param EventEmitterInterface $eventEmitter
     */
    public function __construct(
        StructureListRegistry $structureListRegistry,
        ClasslikeConverter $classlikeConverter,
        EventEmitterInterface $eventEmitter
    ) {
        $this->structureListRegistry = $structureListRegistry;
        $this->classlikeConverter = $classlikeConverter;
        $this->eventEmitter = $eventEmitter;

        $this->setup();
    }

    /**
     * @return void
     */
    protected function setup(): void
    {
        $this->eventEmitter->on(IndexingEventName::STRUCTURE_UPDATED, function (Structures\Structure $structure) {
            $this->structureListRegistry->add($this->classlikeConverter->convert($structure));
        });

        $this->eventEmitter->on(IndexingEventName::STRUCTURE_REMOVED, function (Structures\Structure $structure) {
            $this->structureListRegistry->remove($this->classlikeConverter->convert($structure));
        });
    }
}
