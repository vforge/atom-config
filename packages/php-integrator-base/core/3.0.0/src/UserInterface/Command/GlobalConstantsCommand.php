<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;

use PhpIntegrator\Analysis\ConstantListProviderInterface;

/**
 * Command that shows a list of global constants.
 */
class GlobalConstantsCommand extends AbstractCommand
{
    /**
     * @var ConstantListProviderInterface
     */
    private $constantListProvider;

    /**
     * @param ConstantListProviderInterface $constantListProvider
     */
    public function __construct(ConstantListProviderInterface $constantListProvider)
    {
        $this->constantListProvider = $constantListProvider;
    }

    /**
     * @inheritDoc
     */
    public function execute(ArrayAccess $arguments)
    {
        return $this->getGlobalConstants();
    }

    /**
     * @return array
     */
    public function getGlobalConstants(): array
    {
        return $this->constantListProvider->getAll();
    }
}
