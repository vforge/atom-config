<?php

namespace PhpIntegrator\Linting;

use PhpIntegrator\Analysis\DocblockAnalyzer;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;
use PhpIntegrator\Analysis\Typing\ParameterDocblockTypeSemanticEqualityChecker;

use PhpIntegrator\Analysis\Visiting\OutlineFetchingVisitor;

use PhpIntegrator\Parsing\DocblockParser;

/**
 * Analyzes the correctness of docblocks.
 */
class DocblockCorrectnessAnalyzer implements AnalyzerInterface
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var ParameterDocblockTypeSemanticEqualityChecker
     */
    private $parameterDocblockTypeSemanticEqualityChecker;

    /**
     * @var OutlineFetchingVisitor
     */
    private $outlineIndexingVisitor;

    /**
     * @var DocblockParser
     */
    private $docblockParser;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var DocblockAnalyzer
     */
    private $docblockAnalyzer;

    /**
    * @param string                                        $file
     * @param string                                       $code
     * @param ParameterDocblockTypeSemanticEqualityChecker $parameterDocblockTypeSemanticEqualityChecker
     * @param DocblockParser                               $docblockParser
     * @param TypeAnalyzer                                 $typeAnalyzer
     * @param DocblockAnalyzer                             $docblockAnalyzer
     */
    public function __construct(
        string $file,
        string $code,
        ParameterDocblockTypeSemanticEqualityChecker $parameterDocblockTypeSemanticEqualityChecker,
        DocblockParser $docblockParser,
        TypeAnalyzer $typeAnalyzer,
        DocblockAnalyzer $docblockAnalyzer
    ) {
        $this->file = $file;
        $this->parameterDocblockTypeSemanticEqualityChecker = $parameterDocblockTypeSemanticEqualityChecker;
        $this->docblockParser = $docblockParser;
        $this->typeAnalyzer = $typeAnalyzer;
        $this->docblockAnalyzer = $docblockAnalyzer;

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
        return array_merge(
            $this->getVarTagMissingErrors(),
            $this->getParameterMissingErrors(),
            $this->getParameterTypeMismatchErrors(),
            $this->getSuperfluousParameterErrors()
        );
    }

    /**
     * @inheritDoc
     */
    public function getWarnings(): array
    {
        return array_merge(
            $this->getDeprecatedCategoryTagWarnings(),
            $this->getDeprecatedSubpackageTagWarnings(),
            $this->getDeprecatedLinkTagWarnings()
        );
    }

    /**
     * @return array
     */
    protected function getVarTagMissingErrors(): array
    {
        $errors = [];

        foreach ($this->outlineIndexingVisitor->getStructures() as $structure) {
            $errors = array_merge($errors, $this->getVarTagMissingErrorsForStructure($structure));
        }

        return $errors;
    }

    /**
     * @param array $structure
     *
     * @return array
     */
    protected function getVarTagMissingErrorsForStructure(array $structure): array
    {
        $errors = [];

        foreach ($structure['properties'] as $property) {
            $errors = array_merge($errors, $this->getVarTagMissingErrorsForProperty($structure, $property));
        }

        foreach ($structure['constants'] as $constant) {
            $errors = array_merge($errors, $this->getVarTagMissingErrorsForClassConstant($structure, $constant));
        }

        return $errors;
    }

    /**
     * @param array $structure
     * @param array $property
     *
     * @return array
     */
    protected function getVarTagMissingErrorsForProperty(array $structure, array $property): array
    {
        if (!$property['docComment']) {
            return [];
        }

        $result = $this->docblockParser->parse($property['docComment'], [DocblockParser::VAR_TYPE], $property['name']);

        if (isset($result['var']['$' . $property['name']]['type'])) {
            return [];
        }

        return [
            [
                'message' => "Property docblock is missing @var tag.",
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
    protected function getVarTagMissingErrorsForClassConstant(array $structure, array $constant): array
    {
        if (!$constant['docComment']) {
            return [];
        }

        $result = $this->docblockParser->parse($constant['docComment'], [DocblockParser::VAR_TYPE], $constant['name']);

        if (isset($result['var']['$' . $constant['name']]['type'])) {
            return [];
        }

        return [
            [
                'message' => "Constant docblock is missing @var tag.",
                'start'   => $constant['startPosName'],
                'end'     => $constant['endPosName'] + 1
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getParameterMissingErrors(): array
    {
        $errors = [];

        foreach ($this->outlineIndexingVisitor->getStructures() as $structure) {
            $errors = array_merge($errors, $this->getParameterMissingErrorsForStructure($structure));
        }

        foreach ($this->outlineIndexingVisitor->getGlobalFunctions() as $globalFunction) {
            $errors = array_merge($errors, $this->getParameterMissingErrorsForGlobalFunction($globalFunction));
        }

        return $errors;
    }

    /**
     * @param array $structure
     *
     * @return array
     */
    protected function getParameterMissingErrorsForStructure(array $structure): array
    {
        $errors = [];

        foreach ($structure['methods'] as $method) {
            $errors = array_merge($errors, $this->getParameterMissingErrorsForMethod($structure, $method));
        }

        return $errors;
    }

    /**
     * @param array $structure
     * @param array $method
     *
     * @return array
     */
    protected function getParameterMissingErrorsForMethod(array $structure, array $method): array
    {
        return $this->getParameterMissingErrorsForGlobalFunction($method);
    }

    /**
     * @param array $globalFunction
     *
     * @return array
     */
    protected function getParameterMissingErrorsForGlobalFunction(array $globalFunction): array
    {
        if (!$globalFunction['docComment']) {
           return [];
       }

       $result = $this->docblockParser->parse(
           $globalFunction['docComment'],
           [DocblockParser::DESCRIPTION, DocblockParser::PARAM_TYPE],
           $globalFunction['name']
       );

       if ($this->docblockAnalyzer->isFullInheritDocSyntax($result['descriptions']['short'])) {
           return [];
       }

       $docblockParameters = $result['params'];

       $issues = [];

       foreach ($globalFunction['parameters'] as $parameter) {
           $dollarName = '$' . $parameter['name'];

           if (isset($docblockParameters[$dollarName])) {
               continue;
           }

           $issues[] = [
               'message' => "Function docblock is missing @param tag for ‘{$dollarName}’.",
               'start'   => $globalFunction['startPosName'],
               'end'     => $globalFunction['endPosName']
           ];
       }

       return $issues;
    }

    /**
     * @return array
     */
    protected function getParameterTypeMismatchErrors(): array
    {
        $errors = [];

        foreach ($this->outlineIndexingVisitor->getStructures() as $structure) {
            $errors = array_merge($errors, $this->getParameterTypeMismatchErrorsForStructure($structure));
        }

        foreach ($this->outlineIndexingVisitor->getGlobalFunctions() as $globalFunction) {
            $errors = array_merge($errors, $this->getParameterTypeMismatchErrorsForGlobalFunction($globalFunction));
        }

        return $errors;
    }

    /**
     * @param array $structure
     *
     * @return array
     */
    protected function getParameterTypeMismatchErrorsForStructure(array $structure): array
    {
        $errors = [];

        foreach ($structure['methods'] as $method) {
            $errors = array_merge($errors, $this->getParameterTypeMismatchErrorsForMethod($structure, $method));
        }

        return $errors;
    }

    /**
     * @param array $structure
     * @param array $method
     *
     * @return array
     */
    protected function getParameterTypeMismatchErrorsForMethod(array $structure, array $method): array
    {
        return $this->getParameterTypeMismatchErrorsForGlobalFunction($method);
    }

    /**
     * @param array $globalFunction
     *
     * @return array
     */
    protected function getParameterTypeMismatchErrorsForGlobalFunction(array $globalFunction): array
    {
        if (!$globalFunction['docComment']) {
            return [];
        }

        $result = $this->docblockParser->parse(
            $globalFunction['docComment'],
            [DocblockParser::DESCRIPTION, DocblockParser::PARAM_TYPE],
            $globalFunction['name']
        );

        if ($this->docblockAnalyzer->isFullInheritDocSyntax($result['descriptions']['short'])) {
            return [];
        }

        $docblockParameters = $result['params'];

        $issues = [];

        foreach ($globalFunction['parameters'] as $parameter) {
            $dollarName = '$' . $parameter['name'];

            if (!isset($docblockParameters[$dollarName]) || !$parameter['type']) {
                continue;
            }

            $isTypeConformant = $this->parameterDocblockTypeSemanticEqualityChecker->isEqual(
                $parameter,
                $docblockParameters[$dollarName],
                $this->file,
                $globalFunction['startLine']
            );

            if ($isTypeConformant) {
                continue;
            }

            $issues[] = [
                'message' => "Function docblock has incorrect @param type for ‘{$dollarName}’.",
                'start'   => $globalFunction['startPosName'],
                'end'     => $globalFunction['endPosName']
            ];
        }

        return $issues;
    }

    /**
     * @return array
     */
    protected function getSuperfluousParameterErrors(): array
    {
        $errors = [];

        foreach ($this->outlineIndexingVisitor->getStructures() as $structure) {
            $errors = array_merge($errors, $this->getSuperfluousParameterErrorsForStructure($structure));
        }

        foreach ($this->outlineIndexingVisitor->getGlobalFunctions() as $globalFunction) {
            $errors = array_merge($errors, $this->getSuperfluousParameterErrorsForGlobalFunction($globalFunction));
        }

        return $errors;
    }

    /**
     * @param array $structure
     *
     * @return array
     */
    protected function getSuperfluousParameterErrorsForStructure(array $structure): array
    {
        $errors = [];

        foreach ($structure['methods'] as $method) {
            $errors = array_merge($errors, $this->getSuperfluousParameterErrorsForMethod($structure, $method));
        }

        return $errors;
    }

    /**
     * @param array $structure
     * @param array $method
     *
     * @return array
     */
    protected function getSuperfluousParameterErrorsForMethod(array $structure, array $method): array
    {
        return $this->getSuperfluousParameterErrorsForGlobalFunction($method);
    }

    /**
     * @param array $globalFunction
     *
     * @return array
     */
    protected function getSuperfluousParameterErrorsForGlobalFunction(array $globalFunction): array
    {
        if (!$globalFunction['docComment']) {
            return [];
        }

        $result = $this->docblockParser->parse(
            $globalFunction['docComment'],
            [DocblockParser::DESCRIPTION, DocblockParser::PARAM_TYPE],
            $globalFunction['name']
        );

        if ($this->docblockAnalyzer->isFullInheritDocSyntax($result['descriptions']['short'])) {
            return [];
        }

        $keysFound = [];
        $docblockParameters = $result['params'];

        foreach ($globalFunction['parameters'] as $parameter) {
            $dollarName = '$' . $parameter['name'];

            if (isset($docblockParameters[$dollarName])) {
                $keysFound[] = $dollarName;
            }
        }

        $superfluousParameterNames = array_values(array_diff(array_keys($docblockParameters), $keysFound));

        if (empty($superfluousParameterNames)) {
            return [];
        }

        $superfluousParameterNameString = implode(', ', $superfluousParameterNames);

        return [
            [
                'message' => "Function docblock contains superfluous @param tags for: ‘{$superfluousParameterNameString}’.",
                'start'   => $globalFunction['startPosName'],
                'end'     => $globalFunction['endPosName']
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getDeprecatedCategoryTagWarnings(): array
    {
        $warnings = [];

        foreach ($this->outlineIndexingVisitor->getStructures() as $structure) {
            $warnings = array_merge($warnings, $this->getDeprecatedCategoryTagWarningsForStructure($structure));
        }

        return $warnings;
    }

    /**
     * @return array
     */
    protected function getDeprecatedSubpackageTagWarnings(): array
    {
        $warnings = [];

        foreach ($this->outlineIndexingVisitor->getStructures() as $structure) {
            $warnings = array_merge($warnings, $this->getDeprecatedSubpackageTagWarningsForStructure($structure));
        }

        return $warnings;
    }

    /**
     * @return array
     */
    protected function getDeprecatedLinkTagWarnings(): array
    {
        $warnings = [];

        foreach ($this->outlineIndexingVisitor->getStructures() as $structure) {
            $warnings = array_merge($warnings, $this->getDeprecatedLinkTagWarningsForStructure($structure));
        }

        return $warnings;
    }

    /**
     * @param array $structure
     *
     * @return array
     */
    protected function getDeprecatedCategoryTagWarningsForStructure(array $structure): array
    {
        $result = $this->docblockParser->parse($structure['docComment'], [
            DocblockParser::CATEGORY
        ], $structure['name']);

        if (!$result['category']) {
            return [];
        }

        return [
            [
                'message' => "Classlike docblock contains deprecated @category tag.",
                'start'   => $structure['startPosName'],
                'end'     => $structure['endPosName']
            ]
        ];
    }

    /**
     * @param array $structure
     *
     * @return array
     */
    protected function getDeprecatedSubpackageTagWarningsForStructure(array $structure): array
    {
        $result = $this->docblockParser->parse($structure['docComment'], [
            DocblockParser::SUBPACKAGE
        ], $structure['name']);

        if (!$result['subpackage']) {
            return [];
        }

        return [
            [
                'message' => "Classlike docblock contains deprecated @subpackage tag.",
                'start'   => $structure['startPosName'],
                'end'     => $structure['endPosName']
            ]
        ];
    }

    /**
     * @param array $structure
     *
     * @return array
     *
     * @see https://github.com/phpDocumentor/fig-standards/blob/master/proposed/phpdoc.md#710-link-deprecated
     */
    protected function getDeprecatedLinkTagWarningsForStructure(array $structure): array
    {
        $result = $this->docblockParser->parse($structure['docComment'], [
            DocblockParser::LINK
        ], $structure['name']);

        if (!$result['link']) {
            return [];
        }

        return [
            [
                'message' => "Classlike docblock contains deprecated @link tag. Use @see instead.",
                'start'   => $structure['startPosName'],
                'end'     => $structure['endPosName']
            ]
        ];
    }
}
