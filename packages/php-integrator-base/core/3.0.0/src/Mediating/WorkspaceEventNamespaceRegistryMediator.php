<?php

namespace PhpIntegrator\Mediating;

use Evenement\EventEmitterInterface;

use PhpIntegrator\Analysis\NamespaceListRegistry;

use PhpIntegrator\Indexing\WorkspaceEventName;

/**
 * Mediator that updates the namespace registry when workspace events happen.
 */
class WorkspaceEventNamespaceRegistryMediator
{
    /**
     * @var NamespaceListRegistry
     */
    private $namespaceListRegistry;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @param NamespaceListRegistry  $namespaceListRegistry
     * @param EventEmitterInterface $eventEmitter
     */
    public function __construct(
        NamespaceListRegistry $namespaceListRegistry,
        EventEmitterInterface $eventEmitter
    ) {
        $this->namespaceListRegistry = $namespaceListRegistry;
        $this->eventEmitter = $eventEmitter;

        $this->setup();
    }

    /**
     * @return void
     */
    protected function setup(): void
    {
        $this->eventEmitter->on(WorkspaceEventName::CHANGED, function (string $filePath) {
            $this->namespaceListRegistry->reset();
        });
    }
}
