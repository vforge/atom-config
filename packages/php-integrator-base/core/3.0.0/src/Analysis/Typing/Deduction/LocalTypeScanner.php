<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use PhpIntegrator\Parsing;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

use PhpIntegrator\Analysis\Visiting\ExpressionTypeInfo;
use PhpIntegrator\Analysis\Visiting\ExpressionTypeInfoMap;

use PhpIntegrator\Common\Position;
use PhpIntegrator\Common\FilePosition;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\NameQualificationUtilities\StructureAwareNameResolverFactoryInterface;

use PhpIntegrator\Parsing\DocblockParser;

use PhpIntegrator\Utility\SourceCodeHelpers;

use PhpParser\Node;

/**
 * Scans for types affecting expressions (e.g. variables and properties) in a local scope in a file.
 *
 * This class can be used to scan for types that apply to an expression based on local rules, such as conditionals and
 * type overrides.
 */
class LocalTypeScanner
{
    /**
     * @var DocblockParser
     */
    private $docblockParser;

    /**
     * @var StructureAwareNameResolverFactoryInterface
     */
    private $structureAwareNameResolverFacotry;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @var ForeachNodeLoopValueTypeDeducer
     */
    private $foreachNodeLoopValueTypeDeducer;

    /**
     * @var FunctionLikeParameterTypeDeducer
     */
    private $functionLikeParameterTypeDeducer;

    /**
     * @var ExpressionLocalTypeAnalyzer
     */
    private $expressionLocalTypeAnalyzer;

    /**
     * @param DocblockParser                             $docblockParser
     * @param StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFacotry
     * @param TypeAnalyzer                               $typeAnalyzer
     * @param NodeTypeDeducerInterface                   $nodeTypeDeducer
     * @param ForeachNodeLoopValueTypeDeducer            $foreachNodeLoopValueTypeDeducer
     * @param FunctionLikeParameterTypeDeducer           $functionLikeParameterTypeDeducer
     * @param ExpressionLocalTypeAnalyzer                $expressionLocalTypeAnalyzer
     */
    public function __construct(
        DocblockParser $docblockParser,
        StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFacotry,
        TypeAnalyzer $typeAnalyzer,
        NodeTypeDeducerInterface $nodeTypeDeducer,
        ForeachNodeLoopValueTypeDeducer $foreachNodeLoopValueTypeDeducer,
        FunctionLikeParameterTypeDeducer $functionLikeParameterTypeDeducer,
        ExpressionLocalTypeAnalyzer $expressionLocalTypeAnalyzer
    ) {
        $this->docblockParser = $docblockParser;
        $this->structureAwareNameResolverFacotry = $structureAwareNameResolverFacotry;
        $this->typeAnalyzer = $typeAnalyzer;
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        $this->foreachNodeLoopValueTypeDeducer = $foreachNodeLoopValueTypeDeducer;
        $this->functionLikeParameterTypeDeducer = $functionLikeParameterTypeDeducer;
        $this->expressionLocalTypeAnalyzer = $expressionLocalTypeAnalyzer;
    }

    /**
     * Retrieves the types of a expression based on what's happening to it in a local scope.
     *
     * This can be used to deduce the type of local variables, class properties, ... that are influenced by local
     * assignments, if statements, ...
     *
     * @param Structures\File $file
     * @param string          $code
     * @param string          $expression
     * @param int             $offset
     * @param string[]        $defaultTypes
     *
     * @return string[]
     */
    public function getLocalExpressionTypes(
        Structures\File $file,
        string $code,
        string $expression,
        int $offset,
        array $defaultTypes = []
    ): array {
        $expressionTypeInfoMap = $this->expressionLocalTypeAnalyzer->analyze($code, $offset);
        $offsetLine = SourceCodeHelpers::calculateLineByOffset($code, $offset);

        if (!$expressionTypeInfoMap->has($expression)) {
            return [];
        }

        return $this->getResolvedTypes(
            $expressionTypeInfoMap,
            $expression,
            $file,
            $offsetLine,
            $code,
            $offset,
            $defaultTypes
        );
    }

    /**
     * Retrieves a list of fully resolved types for the variable.
     *
     * @param ExpressionTypeInfoMap $expressionTypeInfoMap
     * @param string                $expression
     * @param Structures\File       $file
     * @param int                   $line
     * @param string                $code
     * @param int                   $offset
     * @param string[]              $defaultTypes
     *
     * @return string[]
     */
    protected function getResolvedTypes(
        ExpressionTypeInfoMap $expressionTypeInfoMap,
        string $expression,
        Structures\File $file,
        int $line,
        string $code,
        int $offset,
        array $defaultTypes = []
    ): array {
        $types = $this->getUnreferencedTypes($expressionTypeInfoMap, $expression, $file, $code, $offset, $defaultTypes);

        $expressionTypeInfo = $expressionTypeInfoMap->get($expression);

        $resolvedTypes = [];

        foreach ($types as $type) {
            $typeLine = $expressionTypeInfo->hasBestTypeOverrideMatch() ?
                $expressionTypeInfo->getBestTypeOverrideMatchLine() :
                $line;

            $filePosition = new FilePosition($file->getPath(), new Position($typeLine, 0));

            $resolvedTypes[] = $this->structureAwareNameResolverFacotry->create($filePosition)->resolve(
                $type,
                $filePosition
            );
        }

        return $resolvedTypes;
    }

    /**
     * Retrieves a list of types for the variable, with any referencing types (self, static, $this, ...)
     * resolved to their actual types.
     *
     * @param ExpressionTypeInfoMap $expressionTypeInfoMap
     * @param string                $expression
     * @param Structures\File       $file
     * @param string                $code
     * @param int                   $offset
     * @param string[]              $defaultTypes
     *
     * @return string[]
     */
    protected function getUnreferencedTypes(
        ExpressionTypeInfoMap $expressionTypeInfoMap,
        string $expression,
        Structures\File $file,
        string $code,
        int $offset,
        array $defaultTypes = []
    ): array {
        $expressionTypeInfo = $expressionTypeInfoMap->get($expression);

        $types = $this->getTypes($expressionTypeInfo, $expression, $file, $code, $offset, $defaultTypes);

        $unreferencedTypes = [];

        $selfType = $this->deduceTypesFromSelf($file, $code, $offset);
        $selfType = array_shift($selfType);
        $selfType = $selfType ?: '';

        $staticType = $this->deduceTypesFromStatic($file, $code, $offset);
        $staticType = array_shift($staticType);
        $staticType = $staticType ?: '';

        foreach ($types as $type) {
            $type = $this->typeAnalyzer->interchangeSelfWithActualType($type, $selfType);
            $type = $this->typeAnalyzer->interchangeStaticWithActualType($type, $staticType);
            $type = $this->typeAnalyzer->interchangeThisWithActualType($type, $staticType);

            $unreferencedTypes[] = $type;
        }

        return $unreferencedTypes;
    }

    /**
     * @param Structures\File $file
     * @param string          $code
     * @param int             $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromSelf(Structures\File $file, string $code, int $offset): array
    {
        $dummyNode = new Parsing\Node\Keyword\Self_();

        return $this->nodeTypeDeducer->deduce($dummyNode, $file, $code, $offset);
    }

    /**
     * @param Structures\File $file
     * @param string          $code
     * @param int             $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromStatic(Structures\File $file, string $code, int $offset): array
    {
        $dummyNode = new Parsing\Node\Keyword\Static_();

        return $this->nodeTypeDeducer->deduce($dummyNode, $file, $code, $offset);
    }

    /**
     * @param ExpressionTypeInfo $expressionTypeInfo
     * @param string             $expression
     * @param Structures\File    $file
     * @param string             $code
     * @param int                $offset
     * @param string[]           $defaultTypes
     *
     * @return string[]
     */
    protected function getTypes(
        ExpressionTypeInfo $expressionTypeInfo,
        string $expression,
        Structures\File $file,
        string $code,
        int $offset,
        array $defaultTypes = []
    ): array {
        if ($expressionTypeInfo->hasBestTypeOverrideMatch()) {
            return $this->typeAnalyzer->getTypesForTypeSpecification($expressionTypeInfo->getBestTypeOverrideMatch());
        }

        $types = $defaultTypes;

        if ($expressionTypeInfo->hasBestMatch()) {
            $types = $this->getTypesForBestMatchNode($expression, $expressionTypeInfo->getBestMatch(), $file, $code, $offset);
        }

        return $expressionTypeInfo->getTypePossibilityMap()->determineApplicableTypes($types);
    }

    /**
     * @param string          $expression
     * @param Node            $node
     * @param Structures\File $file
     * @param string          $code
     * @param int             $offset
     *
     * @return string[]
     */
    protected function getTypesForBestMatchNode(
        string $expression,
        Node $node,
        Structures\File $file,
        string $code,
        int $offset
    ): array {
        if ($node instanceof Node\Stmt\Foreach_) {
            return $this->foreachNodeLoopValueTypeDeducer->deduce($node, $file, $code, $offset);
        } elseif ($node instanceof Node\FunctionLike) {
            return $this->deduceTypesFromFunctionLikeParameter($node, $expression, $file, $code, $offset);
        }

        return $this->nodeTypeDeducer->deduce($node, $file, $code, $offset);
    }

    /**
     * @param Node\FunctionLike $node
     * @param string            $parameterName
     * @param Structures\File   $file
     * @param string            $code
     * @param int               $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromFunctionLikeParameter(
        Node\FunctionLike $node,
        string $parameterName,
        Structures\File $file,
        string $code,
        int $offset
    ): array {
        foreach ($node->getParams() as $param) {
            if ($param->var->name === mb_substr($parameterName, 1)) {
                $this->functionLikeParameterTypeDeducer->setFunctionDocblock($node->getDocComment());

                return $this->functionLikeParameterTypeDeducer->deduce($param, $file, $code, $offset);
            }
        }

        return [];
    }
}
