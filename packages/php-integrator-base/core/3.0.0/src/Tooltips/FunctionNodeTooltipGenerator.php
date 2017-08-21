<?php

namespace PhpIntegrator\Tooltips;

use UnexpectedValueException;

use PhpIntegrator\Analysis\Node\FunctionFunctionInfoRetriever;

use PhpParser\Node;

/**
 * Provides tooltips for {@see Node\Stmt\Function_} nodes.
 */
class FunctionNodeTooltipGenerator
{
    /**
     * @var FunctionTooltipGenerator
     */
    private $functionTooltipGenerator;

    /**
     * @var FunctionFunctionInfoRetriever
     */
    private $functionCallFunctionInfoRetriever;

    /**
     * @param FunctionTooltipGenerator      $functionTooltipGenerator
     * @param FunctionFunctionInfoRetriever $functionCallFunctionInfoRetriever
     */
    public function __construct(
        FunctionTooltipGenerator $functionTooltipGenerator,
        FunctionFunctionInfoRetriever $functionCallFunctionInfoRetriever
    ) {
        $this->functionTooltipGenerator = $functionTooltipGenerator;
        $this->functionCallFunctionInfoRetriever = $functionCallFunctionInfoRetriever;
    }

    /**
     * @param Node\Stmt\Function_ $node
     *
     * @throws UnexpectedValueException when the function was not found.
     *
     * @return string
     */
    public function generate(Node\Stmt\Function_ $node): string
    {
        $info = $this->functionCallFunctionInfoRetriever->retrieve($node);

        return $this->functionTooltipGenerator->generate($info);
    }
}
