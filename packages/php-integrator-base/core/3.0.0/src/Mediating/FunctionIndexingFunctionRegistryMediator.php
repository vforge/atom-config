<?php

namespace PhpIntegrator\Mediating;

use Evenement\EventEmitterInterface;

use PhpIntegrator\Analysis\FunctionListRegistry;

use PhpIntegrator\Analysis\Conversion\FunctionConverter;

use PhpIntegrator\Indexing\Structures;
use PhpIntegrator\Indexing\IndexingEventName;

/**
 * Mediator that updates the function registry when function indexing events happen.
 */
class FunctionIndexingFunctionRegistryMediator
{
    /**
     * @var FunctionListRegistry
     */
    private $functionListRegistry;

    /**
     * @var FunctionConverter
     */
    private $functionConverter;

    /**
     * @var EventEmitterInterface
     */
    private $eventEmitter;

    /**
     * @param FunctionListRegistry  $functionListRegistry
     * @param FunctionConverter     $functionConverter
     * @param EventEmitterInterface $eventEmitter
     */
    public function __construct(
        FunctionListRegistry $functionListRegistry,
        FunctionConverter $functionConverter,
        EventEmitterInterface $eventEmitter
    ) {
        $this->functionListRegistry = $functionListRegistry;
        $this->functionConverter = $functionConverter;
        $this->eventEmitter = $eventEmitter;

        $this->setup();
    }

    /**
     * @return void
     */
    protected function setup(): void
    {
        $this->eventEmitter->on(IndexingEventName::FUNCTION_UPDATED, function (Structures\Function_ $function) {
            $this->functionListRegistry->add($this->functionConverter->convert($function));
        });

        $this->eventEmitter->on(IndexingEventName::FUNCTION_REMOVED, function (Structures\Function_ $function) {
            $this->functionListRegistry->remove($this->functionConverter->convert($function));
        });
    }
}
