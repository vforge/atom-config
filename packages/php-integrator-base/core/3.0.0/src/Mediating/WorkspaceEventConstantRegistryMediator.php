<?php

namespace PhpIntegrator\Mediating;

use Evenement\EventEmitterInterface;

use PhpIntegrator\Analysis\ConstantListRegistry;

use PhpIntegrator\Indexing\WorkspaceEventName;

/**
 * Mediator that updates the constant registry when workspace events happen.
 */
class WorkspaceEventConstantRegistryMediator
{
    /**
     * @var ConstantListRegistry
     */
    private $constantListRegistry;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @param ConstantListRegistry  $constantListRegistry
     * @param EventEmitterInterface $eventEmitter
     */
    public function __construct(
        ConstantListRegistry $constantListRegistry,
        EventEmitterInterface $eventEmitter
    ) {
        $this->constantListRegistry = $constantListRegistry;
        $this->eventEmitter = $eventEmitter;

        $this->setup();
    }

    /**
     * @return void
     */
    protected function setup(): void
    {
        $this->eventEmitter->on(WorkspaceEventName::CHANGED, function (string $filePath) {
            $this->constantListRegistry->reset();
        });
    }
}
