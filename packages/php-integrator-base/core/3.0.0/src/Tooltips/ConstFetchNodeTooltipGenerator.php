<?php

namespace PhpIntegrator\Tooltips;

use UnexpectedValueException;

use PhpIntegrator\Analysis\ConstantListProviderInterface;

use PhpIntegrator\Analysis\Node\ConstNameNodeFqsenDeterminer;

use PhpParser\Node;

/**
 * Provides tooltips for {@see Node\Expr\ConstFetch} nodes.
 */
class ConstFetchNodeTooltipGenerator
{
    /**
     * @var ConstantTooltipGenerator
     */
    private $constantTooltipGenerator;

    /**
     * @var ConstNameNodeFqsenDeterminer
     */
    private $constFetchNodeFqsenDeterminer;

    /**
     * @var ConstantListProviderInterface
     */
    private $constantListProvider;

    /**
     * @param ConstantTooltipGenerator      $constantTooltipGenerator
     * @param ConstNameNodeFqsenDeterminer  $constFetchNodeFqsenDeterminer
     * @param ConstantListProviderInterface $constantListProvider
     */
    public function __construct(
        ConstantTooltipGenerator $constantTooltipGenerator,
        ConstNameNodeFqsenDeterminer $constFetchNodeFqsenDeterminer,
        ConstantListProviderInterface $constantListProvider
    ) {
        $this->constantTooltipGenerator = $constantTooltipGenerator;
        $this->constFetchNodeFqsenDeterminer = $constFetchNodeFqsenDeterminer;
        $this->constantListProvider = $constantListProvider;
    }

    /**
     * @param Node\Expr\ConstFetch $node
     *
     * @throws UnexpectedValueException when the constant was not found.
     *
     * @return string
     */
    public function generate(Node\Expr\ConstFetch $node): string
    {
        $fqsen = $this->constFetchNodeFqsenDeterminer->determine($node->name);

        $info = $this->getConstantInfo($fqsen);

        return $this->constantTooltipGenerator->generate($info);
    }

    /**
     * @param string $fullyQualifiedName
     *
     * @throws UnexpectedValueException
     *
     * @return array
     */
    protected function getConstantInfo(string $fullyQualifiedName): array
    {
        $functions = $this->constantListProvider->getAll();

        if (!isset($functions[$fullyQualifiedName])) {
            throw new UnexpectedValueException('No data found for function with name ' . $fullyQualifiedName);
        }

        return $functions[$fullyQualifiedName];
    }
}
