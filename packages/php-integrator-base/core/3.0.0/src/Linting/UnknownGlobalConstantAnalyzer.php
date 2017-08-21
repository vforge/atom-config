<?php

namespace PhpIntegrator\Linting;

use PhpIntegrator\Analysis\Node\ConstNameNodeFqsenDeterminer;

use PhpIntegrator\Analysis\Visiting\GlobalConstantUsageFetchingVisitor;

use PhpIntegrator\NameQualificationUtilities\ConstantPresenceIndicatorInterface;

/**
 * Looks for unknown global constant names.
 */
class UnknownGlobalConstantAnalyzer implements AnalyzerInterface
{
    /**
     * @var ConstantPresenceIndicatorInterface
     */
    private $constantPresenceIndicator;

    /**
     * @var GlobalConstantUsageFetchingVisitor
     */
    private $globalConstantUsageFetchingVisitor;

    /**
     * @param ConstantPresenceIndicatorInterface $constantPresenceIndicator
     */
    public function __construct(ConstantPresenceIndicatorInterface $constantPresenceIndicator)
    {
        $this->constantPresenceIndicator = $constantPresenceIndicator;

        $this->globalConstantUsageFetchingVisitor = new GlobalConstantUsageFetchingVisitor();
    }

    /**
     * @inheritDoc
     */
    public function getVisitors(): array
    {
        return [
            $this->globalConstantUsageFetchingVisitor
        ];
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        $globalConstants = $this->globalConstantUsageFetchingVisitor->getGlobalConstantList();

        $unknownGlobalConstants = [];

        // TODO: Inject this.
        $determiner = new ConstNameNodeFqsenDeterminer($this->constantPresenceIndicator);

        foreach ($globalConstants as $node) {
            $fqsen = $determiner->determine($node->name);

            if ($this->constantPresenceIndicator->isPresent($fqsen)) {
                continue;
            }

            $unknownGlobalConstants[] = [
                'message' => "Constant is not defined or imported anywhere.",
                'start'   => $node->getAttribute('startFilePos') ? $node->getAttribute('startFilePos')   : null,
                'end'     => $node->getAttribute('endFilePos')   ? $node->getAttribute('endFilePos') + 1 : null
            ];
        }

        return $unknownGlobalConstants;
    }

    /**
     * @inheritDoc
     */
    public function getWarnings(): array
    {
        return [];
    }
}
