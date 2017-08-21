<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;

use PhpIntegrator\Analysis\FunctionListProviderInterface;

/**
 * Command that shows a list of global functions.
 */
class GlobalFunctionsCommand extends AbstractCommand
{
    /**
     * @var FunctionListProviderInterface
     */
    private $functionListProvider;

    /**
     * @param FunctionListProviderInterface $functionListProvider
     */
    public function __construct(FunctionListProviderInterface $functionListProvider)
    {
        $this->functionListProvider = $functionListProvider;
    }

    /**
     * @inheritDoc
     */
    public function execute(ArrayAccess $arguments)
    {
        return $this->getGlobalFunctions();
    }

     /**
      * @return array
      */
    public function getGlobalFunctions(): array
    {
        return $this->functionListProvider->getAll();
    }
}
