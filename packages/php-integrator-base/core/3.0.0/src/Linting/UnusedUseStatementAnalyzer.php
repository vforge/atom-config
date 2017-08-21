<?php

namespace PhpIntegrator\Linting;

use LogicException;

use PhpIntegrator\Analysis\Visiting\UseStatementKind;
use PhpIntegrator\Analysis\Visiting\ClassUsageFetchingVisitor;
use PhpIntegrator\Analysis\Visiting\UseStatementFetchingVisitor;
use PhpIntegrator\Analysis\Visiting\DocblockClassUsageFetchingVisitor;
use PhpIntegrator\Analysis\Visiting\GlobalFunctionUsageFetchingVisitor;
use PhpIntegrator\Analysis\Visiting\GlobalConstantUsageFetchingVisitor;

use PhpIntegrator\Parsing\DocblockParser;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

/**
 * Looks for unused use statements.
 */
class UnusedUseStatementAnalyzer implements AnalyzerInterface
{
    /**
     * @var ClassUsageFetchingVisitor
     */
    private $classUsageFetchingVisitor;

    /**
     * @var UseStatementFetchingVisitor
     */
    private $useStatementFetchingVisitor;

    /**
     * @var GlobalConstantUsageFetchingVisitor
     */
    private $globalConstantUsageFetchingVisitor;

    /**
     * @var GlobalFunctionUsageFetchingVisitor
     */
    private $globalFunctionUsageFetchingVisitor;

    /**
     * @var DocblockClassUsageFetchingVisitor
     */
    private $docblockClassUsageFetchingVisitor;

    /**
     * Constructor.
     *
     * @param TypeAnalyzer   $typeAnalyzer
     * @param DocblockParser $docblockParser
     * @param string         $code
     */
    public function __construct(TypeAnalyzer $typeAnalyzer, DocblockParser $docblockParser, string $code)
    {
        $this->classUsageFetchingVisitor = new ClassUsageFetchingVisitor($typeAnalyzer);
        $this->useStatementFetchingVisitor = new UseStatementFetchingVisitor($code);
        $this->globalConstantUsageFetchingVisitor = new GlobalConstantUsageFetchingVisitor();
        $this->globalFunctionUsageFetchingVisitor = new GlobalFunctionUsageFetchingVisitor();
        $this->docblockClassUsageFetchingVisitor = new DocblockClassUsageFetchingVisitor($typeAnalyzer, $docblockParser);
    }

    /**
     * @inheritDoc
     */
    public function getVisitors(): array
    {
        return [
            $this->classUsageFetchingVisitor,
            $this->useStatementFetchingVisitor,
            $this->docblockClassUsageFetchingVisitor,
            $this->globalConstantUsageFetchingVisitor,
            $this->globalFunctionUsageFetchingVisitor
        ];
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getWarnings(): array
    {
        $unusedUseStatements = array_merge(
            $this->getWarningsForClasses(),
            $this->getWarningsForConstants(),
            $this->getWarningsForFunctions()
        );

        return $unusedUseStatements;
    }

    /**
     * @return array
     */
    protected function getWarningsForClasses(): array
    {
        // Cross-reference the found class names against the class map.
        $namespaces = $this->useStatementFetchingVisitor->getNamespaces();

        $classUsages = array_merge(
            $this->classUsageFetchingVisitor->getClassUsageList(),
            $this->docblockClassUsageFetchingVisitor->getClassUsageList()
        );

        foreach ($classUsages as $classUsage) {
            $relevantAlias = $classUsage['firstPart'];

            $index = $this->getRelevantNamespaceIndexForLine($namespaces, $classUsage['line']);

            if (!$classUsage['isFullyQualified'] &&
                isset($namespaces[$index]['useStatements'][$relevantAlias]) &&
                $namespaces[$index]['useStatements'][$relevantAlias]['kind'] === UseStatementKind::TYPE_CLASSLIKE
            ) {
                // Mark the accompanying used statement, if any, as used.
                $namespaces[$index]['useStatements'][$relevantAlias]['used'] = true;
            }
        }

        $unusedUseStatements = [];

        foreach ($namespaces as $namespaceData) {
            $useStatementMap = $namespaceData['useStatements'];

            foreach ($useStatementMap as $alias => $data) {
                if (
                    (!array_key_exists('used', $data) || !$data['used']) &&
                    $data['kind'] === UseStatementKind::TYPE_CLASSLIKE
                ) {
                    $unusedUseStatements[] = [
                        'message' => "Classlike is imported, but not used anywhere.",
                        'start'   => $data['start'],
                        'end'     => $data['end']
                    ];
                }
            }
        }

        return $unusedUseStatements;
    }

    /**
     * @return array
     */
    protected function getWarningsForConstants(): array
    {
        $unknownClasses = [];
        $namespaces = $this->useStatementFetchingVisitor->getNamespaces();

        $constantUsages = $this->globalConstantUsageFetchingVisitor->getGlobalConstantList();

        foreach ($constantUsages as $node) {
            $relevantAlias = $node->name->getFirst();

            $index = $this->getRelevantNamespaceIndexForLine($namespaces, $node->getAttribute('startLine'));

            if (!$node->name->isFullyQualified() &&
                isset($namespaces[$index]['useStatements'][$relevantAlias]) &&
                $namespaces[$index]['useStatements'][$relevantAlias]['kind'] === UseStatementKind::TYPE_CONSTANT
            ) {
                // Mark the accompanying used statement, if any, as used.
                $namespaces[$index]['useStatements'][$relevantAlias]['used'] = true;
            }
        }

        $unusedUseStatements = [];

        foreach ($namespaces as $namespaceData) {
            $useStatementMap = $namespaceData['useStatements'];

            foreach ($useStatementMap as $alias => $data) {
                if (
                    (!array_key_exists('used', $data) || !$data['used']) &&
                    $data['kind'] === UseStatementKind::TYPE_CONSTANT
                ) {
                    $unusedUseStatements[] = [
                        'message' => "Constant is imported, but not used anywhere.",
                        'start'   => $data['start'],
                        'end'     => $data['end']
                    ];
                }
            }
        }

        return $unusedUseStatements;
    }

    /**
     * @return array
     */
    protected function getWarningsForFunctions(): array
    {
        $unknownClasses = [];
        $namespaces = $this->useStatementFetchingVisitor->getNamespaces();

        $functionUsages = $this->globalFunctionUsageFetchingVisitor->getGlobalFunctionCallList();

        foreach ($functionUsages as $node) {
            $relevantAlias = $node->name->getFirst();

            $index = $this->getRelevantNamespaceIndexForLine($namespaces, $node->getAttribute('startLine'));

            if (!$node->name->isFullyQualified() &&
                isset($namespaces[$index]['useStatements'][$relevantAlias]) &&
                $namespaces[$index]['useStatements'][$relevantAlias]['kind'] === UseStatementKind::TYPE_FUNCTION
            ) {
                // Mark the accompanying used statement, if any, as used.
                $namespaces[$index]['useStatements'][$relevantAlias]['used'] = true;
            }
        }

        $unusedUseStatements = [];

        foreach ($namespaces as $namespaceData) {
            $useStatementMap = $namespaceData['useStatements'];

            foreach ($useStatementMap as $alias => $data) {
                if (
                    (!array_key_exists('used', $data) || !$data['used']) &&
                    $data['kind'] === UseStatementKind::TYPE_FUNCTION
                ) {
                    $unusedUseStatements[] = [
                        'message' => "Function is imported, but not used anywhere.",
                        'start'   => $data['start'],
                        'end'     => $data['end']
                    ];
                }
            }
        }

        return $unusedUseStatements;
    }

    /**
     * @param array $namespaces
     * @param int   $line
     *
     * @return int
     */
    protected function getRelevantNamespaceIndexForLine(array $namespaces, int $line): int
    {
        foreach ($namespaces as $index => $namespace) {
            if ($this->lineLiesWithinNamespaceRange($line, $namespace)) {
                return $index;
            }
        }

        throw new LogicException('Sanity check failed: should always have at least one namespace structure');
    }

    /**
     * @param int   $line
     * @param array $namespace
     *
     * @return bool
     */
    protected function lineLiesWithinNamespaceRange(int $line, array $namespace): bool
    {
        return (
            $line >= $namespace['startLine'] &&
            ($line <= $namespace['endLine'] || $namespace['endLine'] === null)
        );
    }
}
