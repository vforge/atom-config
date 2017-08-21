<?php

namespace PhpIntegrator\Linting;

use PhpIntegrator\Analysis\ClasslikeInfoBuilder;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

use PhpIntegrator\Analysis\Visiting\OutlineFetchingVisitor;

/**
 * Analyzes code to search for missing docblocks.
 */
class DocblockMissingAnalyzer implements AnalyzerInterface
{
    /**
     * @var OutlineFetchingVisitor
     */
    private $outlineIndexingVisitor;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var ClasslikeInfoBuilder
     */
    private $classlikeInfoBuilder;

    /**
     * @param string               $code
     * @param TypeAnalyzer         $typeAnalyzer
     * @param ClasslikeInfoBuilder $classlikeInfoBuilder
     */
    public function __construct(string $code, TypeAnalyzer $typeAnalyzer, ClasslikeInfoBuilder $classlikeInfoBuilder)
    {
        $this->classlikeInfoBuilder = $classlikeInfoBuilder;

        $this->outlineIndexingVisitor = new OutlineFetchingVisitor($typeAnalyzer, $code);
    }

    /**
     * @inheritDoc
     */
    public function getVisitors(): array
    {
        return [
            $this->outlineIndexingVisitor
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
        return $this->getMissingDocumentationWarnings();
    }

    /**
     * @return array
     */
    protected function getMissingDocumentationWarnings(): array
    {
        $warnings = [];

        foreach ($this->outlineIndexingVisitor->getStructures() as $structure) {
            $warnings = array_merge($warnings, $this->getMissingDocumentationWarningsForStructure($structure));
        }

        foreach ($this->outlineIndexingVisitor->getGlobalFunctions() as $globalFunction) {
            $warnings = array_merge($warnings, $this->getMissingDocumentationWarningsForGlobalFunction($globalFunction));
        }

        return $warnings;
    }

    /**
     * @param array $structure
     *
     * @return array
     */
    protected function getMissingDocumentationWarningsForStructure(array $structure): array
    {
        $warnings = [];

        $classInfo = $this->classlikeInfoBuilder->getClasslikeInfo($structure['fqcn']);

        if ($classInfo && !$classInfo['hasDocumentation']) {
            $warnings[] = [
                'message' => "Documentation for classlike is missing.",
                'start'   => $structure['startPosName'],
                'end'     => $structure['endPosName']
            ];
        }

        foreach ($structure['methods'] as $method) {
            $warnings = array_merge($warnings, $this->getMissingDocumentationWarningsForMethod($structure, $method));
        }

        foreach ($structure['properties'] as $property) {
            $warnings = array_merge($warnings, $this->getMissingDocumentationWarningsForProperty($structure, $property));
        }

        foreach ($structure['constants'] as $constant) {
            $warnings = array_merge($warnings, $this->getMissingDocumentationWarningsForClassConstant($structure, $constant));
        }

        return $warnings;
    }

    /**
     * @param array $globalFunction
     *
     * @return array
     */
    protected function getMissingDocumentationWarningsForGlobalFunction(array $globalFunction): array
    {
        if ($globalFunction['docComment']) {
            return [];
        }

        return [
            [
                'message' => "Documentation for function is missing.",
                'start'   => $globalFunction['startPosName'],
                'end'     => $globalFunction['endPosName']
            ]
        ];
    }

    /**
     * @param array $structure
     * @param array $method
     *
     * @return array
     */
    protected function getMissingDocumentationWarningsForMethod(array $structure, array $method): array
    {
        if ($method['docComment']) {
            return [];
        }

        $classInfo = $this->classlikeInfoBuilder->getClasslikeInfo($structure['fqcn']);

        if (!$classInfo ||
            !isset($classInfo['methods'][$method['name']]) ||
            $classInfo['methods'][$method['name']]['hasDocumentation']
        ) {
            return [];
        }

        return [
            [
                'message' => "Documentation for method is missing.",
                'start'   => $method['startPosName'],
                'end'     => $method['endPosName']
            ]
        ];
    }

    /**
     * @param array $structure
     * @param array $property
     *
     * @return array
     */
    protected function getMissingDocumentationWarningsForProperty(array $structure, array $property): array
    {
        if ($property['docComment']) {
            return [];
        }

        $classInfo = $this->classlikeInfoBuilder->getClasslikeInfo($structure['fqcn']);

        if (!$classInfo ||
            !isset($classInfo['properties'][$property['name']]) ||
            $classInfo['properties'][$property['name']]['hasDocumentation']
        ) {
            return [];
        }

        return [
            [
                'message' => "Documentation for property is missing.",
                'start'   => $property['startPosName'],
                'end'     => $property['endPosName']
            ]
        ];
    }

    /**
     * @param array $structure
     * @param array $constant
     *
     * @return array
     */
    protected function getMissingDocumentationWarningsForClassConstant(array $structure, array $constant): array
    {
        if ($constant['docComment']) {
            return [];
        }

        return [
            [
                'message' => "Documentation for constant is missing.",
                'start'   => $constant['startPosName'],
                'end'     => $constant['endPosName']
            ]
        ];
    }
}
