<?php

namespace PhpIntegrator\Linting;

use PhpIntegrator\Analysis\Node\FunctionNameNodeFqsenDeterminer;

use PhpIntegrator\Analysis\Visiting\GlobalFunctionUsageFetchingVisitor;

use PhpIntegrator\NameQualificationUtilities\FunctionPresenceIndicatorInterface;

/**
 * Looks for unknown global function names (i.e. used during calls).
 */
class UnknownGlobalFunctionAnalyzer implements AnalyzerInterface
{
    /**
     * @var GlobalFunctionUsageFetchingVisitor
     */
    private $globalFunctionUsageFetchingVisitor;

    /**
     * @var FunctionPresenceIndicatorInterface
     */
    private $functionPresenceIndicator;

    /**
     * @param FunctionPresenceIndicatorInterface $functionPresenceIndicator
     */
    public function __construct(FunctionPresenceIndicatorInterface $functionPresenceIndicator)
    {
        $this->functionPresenceIndicator = $functionPresenceIndicator;

        $this->globalFunctionUsageFetchingVisitor = new GlobalFunctionUsageFetchingVisitor();
    }

    /**
     * @inheritDoc
     */
    public function getVisitors(): array
    {
        return [
            $this->globalFunctionUsageFetchingVisitor
        ];
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        $globalFunctions = $this->globalFunctionUsageFetchingVisitor->getGlobalFunctionCallList();

        $unknownGlobalFunctions = [];

        // TODO: Inject this.
        $determiner = new FunctionNameNodeFqsenDeterminer($this->functionPresenceIndicator);

        foreach ($globalFunctions as $node) {
            $fqsen = $determiner->determine($node->name);

            if ($this->functionPresenceIndicator->isPresent($fqsen)) {
                continue;
            }

            $unknownGlobalFunctions[] = [
                'message' => "Function is not defined or imported anywhere.",
                'start'   => $node->getAttribute('startFilePos') ? $node->getAttribute('startFilePos')   : null,
                'end'     => $node->getAttribute('endFilePos')   ? $node->getAttribute('endFilePos') + 1 : null
            ];
        }

        return $unknownGlobalFunctions;
    }

    /**
     * @inheritDoc
     */
    public function getWarnings(): array
    {
        return [];
    }
}
