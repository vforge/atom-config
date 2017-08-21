<?php

namespace PhpIntegrator\Tooltips;

use UnexpectedValueException;

use PhpIntegrator\Analysis\functionListProviderInterface;

use PhpIntegrator\Analysis\Node\FunctionNameNodeFqsenDeterminer;

use PhpParser\Node;

/**
 * Provides tooltips for {@see Node\Expr\FuncCall} nodes.
 */
class FuncCallNodeTooltipGenerator
{
    /**
     * @var FunctionTooltipGenerator
     */
    private $functionTooltipGenerator;

    /**
     * @var FunctionNameNodeFqsenDeterminer
     */
    private $functionCallNodeFqsenDeterminer;

    /**
     * @var functionListProviderInterface
     */
    private $functionListProvider;

    /**
     * @param FunctionTooltipGenerator        $functionTooltipGenerator
     * @param FunctionNameNodeFqsenDeterminer $functionCallNodeFqsenDeterminer
     * @param functionListProviderInterface   $functionListProvider
     */
    public function __construct(
        FunctionTooltipGenerator $functionTooltipGenerator,
        FunctionNameNodeFqsenDeterminer $functionCallNodeFqsenDeterminer,
        functionListProviderInterface $functionListProvider
    ) {
        $this->functionTooltipGenerator = $functionTooltipGenerator;
        $this->functionCallNodeFqsenDeterminer = $functionCallNodeFqsenDeterminer;
        $this->functionListProvider = $functionListProvider;
    }

    /**
     * @param Node\Expr\FuncCall $node
     *
     * @throws UnexpectedValueException when the function was not found.
     * @throws UnexpectedValueException when a dynamic function call is passed.
     *
     * @return string
     */
    public function generate(Node\Expr\FuncCall $node): string
    {
        if (!$node->name instanceof Node\Name) {
            throw new UnexpectedValueException('Fetching FQSEN of dynamic function calls is not supported');
        }

        $fqsen = $this->functionCallNodeFqsenDeterminer->determine($node->name);

        $info = $this->getFunctionInfo($fqsen);

        return $this->functionTooltipGenerator->generate($info);
    }

    /**
     * @param string $fullyQualifiedName
     *
     * @throws UnexpectedValueException
     *
     * @return array
     */
    protected function getFunctionInfo(string $fullyQualifiedName): array
    {
        $functions = $this->functionListProvider->getAll();

        if (!isset($functions[$fullyQualifiedName])) {
            throw new UnexpectedValueException('No data found for function with name ' . $fullyQualifiedName);
        }

        return $functions[$fullyQualifiedName];
    }
}
