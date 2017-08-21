<?php

namespace PhpIntegrator\Mediating;

use Evenement\EventEmitterInterface;

use PhpIntegrator\Analysis\NamespaceListRegistry;

use PhpIntegrator\Analysis\Conversion\NamespaceConverter;

use PhpIntegrator\Indexing\Structures;
use PhpIntegrator\Indexing\IndexingEventName;

/**
 * Mediator that updates the namespace registry when namespace indexing events happen.
 */
class NamespaceIndexingNamespaceRegistryMediator
{
    /**
     * @var NamespaceListRegistry
     */
    private $namespaceListRegistry;

    /**
     * @var NamespaceConverter
     */
    private $namespaceConverter;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @param NamespaceListRegistry $namespaceListRegistry
     * @param NamespaceConverter    $namespaceConverter
     * @param EventEmitterInterface $eventEmitter
     */
    public function __construct(
        NamespaceListRegistry $namespaceListRegistry,
        NamespaceConverter $namespaceConverter,
        EventEmitterInterface $eventEmitter
    ) {
        $this->namespaceListRegistry = $namespaceListRegistry;
        $this->namespaceConverter = $namespaceConverter;
        $this->eventEmitter = $eventEmitter;

        $this->setup();
    }

    /**
     * @return void
     */
    protected function setup(): void
    {
        $this->eventEmitter->on(IndexingEventName::NAMESPACE_UPDATED, function (Structures\FileNamespace $namespace) {
            $this->namespaceListRegistry->add($this->namespaceConverter->convert($namespace));
        });

        $this->eventEmitter->on(IndexingEventName::NAMESPACE_REMOVED, function (Structures\FileNamespace $namespace) {
            $this->namespaceListRegistry->remove($this->namespaceConverter->convert($namespace));
        });
    }
}
