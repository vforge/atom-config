<?php

namespace PhpIntegrator\Indexing\Visiting;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

use PhpIntegrator\Analysis\Typing\Deduction\NodeTypeDeducerInterface;

use PhpIntegrator\Common\Position;
use PhpIntegrator\Common\FilePosition;

use PhpIntegrator\Indexing\Structures;
use PhpIntegrator\Indexing\StorageInterface;

use PhpIntegrator\NameQualificationUtilities\StructureAwareNameResolverFactoryInterface;

use PhpIntegrator\Parsing\DocblockParser;

use PhpIntegrator\Utility\NodeHelpers;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Visitor that traverses a set of nodes, indexing (global) functions in the process.
 */
final class FunctionIndexingVisitor extends NodeVisitorAbstract
{
    /**
     * @var StructureAwareNameResolverFactoryInterface
     */
    private $structureAwareNameResolverFactory;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var DocblockParser
     */
    private $docblockParser;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @var Structures\File
     */
    private $file;

    /**
     * @var string
     */
    private $code;

    /**
     * @param StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory
     * @param StorageInterface                           $storage
     * @param DocblockParser                             $docblockParser
     * @param TypeAnalyzer                               $typeAnalyzer
     * @param NodeTypeDeducerInterface                   $nodeTypeDeducer
     * @param Structures\File                            $file
     * @param string                                     $code
     */
    public function __construct(
        StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory,
        StorageInterface $storage,
        DocblockParser $docblockParser,
        TypeAnalyzer $typeAnalyzer,
        NodeTypeDeducerInterface $nodeTypeDeducer,
        Structures\File $file,
        string $code
    ) {
        $this->structureAwareNameResolverFactory = $structureAwareNameResolverFactory;
        $this->storage = $storage;
        $this->docblockParser = $docblockParser;
        $this->typeAnalyzer = $typeAnalyzer;
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        $this->file = $file;
        $this->code = $code;
    }

    /**
     * @inheritDoc
     */
    public function beforeTraverse(array $nodes)
    {
        foreach ($this->file->getFunctions() as $function) {
            $this->file->removeFunction($function);

            $this->storage->delete($function);
        }
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Function_) {
            $this->indexFunction($node);
        }
    }

    /**
     * @param Node\Stmt\Function_ $node
     *
     * @return void
     */
    protected function indexFunction(Node\Stmt\Function_ $node): void
    {
        $localType = null;
        $resolvedType = null;
        $returnTypeHint = null;
        $nodeType = $node->getReturnType();

        if ($nodeType instanceof Node\NullableType) {
            $nodeType = $nodeType->type;
            $returnTypeHint = '?';
        }

        if ($nodeType instanceof Node\Name) {
            $localType = NodeHelpers::fetchClassName($nodeType);
            $resolvedType = NodeHelpers::fetchClassName($nodeType->getAttribute('resolvedName'));
            $returnTypeHint .= $resolvedType;
        } elseif ($nodeType instanceof Node\Identifier) {
            $localType = $nodeType->name;
            $resolvedType = $nodeType->name;
            $returnTypeHint .= $resolvedType;
        }

        $docComment = $node->getDocComment() ? $node->getDocComment()->getText() : null;

        $filePosition = new FilePosition($this->file->getPath(), new Position($node->getLine(), 0));

        $documentation = $this->docblockParser->parse($docComment, [
            DocblockParser::THROWS,
            DocblockParser::PARAM_TYPE,
            DocblockParser::DEPRECATED,
            DocblockParser::DESCRIPTION,
            DocblockParser::RETURN_VALUE
        ], $node->name);

        $returnTypes = [];

        if ($documentation && $documentation['return']['type']) {
            $returnTypes = $this->getTypeDataForTypeSpecification($documentation['return']['type'], $filePosition);
        } elseif ($resolvedType) {
            $returnTypes = [
                new Structures\TypeInfo($localType, $resolvedType ?: $localType)
            ];

            if ($node->getReturnType() instanceof Node\NullableType) {
                $returnTypes[] = new Structures\TypeInfo('null', 'null');
            }
        }

        $throws = [];

        foreach ($documentation['throws'] as $throw) {
            $typeData = $this->getTypeDataForTypeSpecification($throw['type'], $filePosition);
            $typeData = array_shift($typeData);

            $throws[] = new Structures\ThrowsInfo(
                $typeData->getType(),
                $typeData->getFqcn(),
                $throw['description'] ?: null
            );
        }

        $function = new Structures\Function_(
            $node->name,
            '\\' . $node->namespacedName->toString(),
            $this->file,
            $node->getLine(),
            $node->getAttribute('endLine'),
            $documentation['deprecated'],
            $documentation['descriptions']['short'] ?: null,
            $documentation['descriptions']['long'] ?: null,
            $documentation['return']['description'] ?: null,
            $returnTypeHint,
            !empty($docComment),
            $throws,
            $returnTypes
        );

        $this->storage->persist($function);

        foreach ($node->getParams() as $param) {
            $typeHint = null;
            $localType = null;
            $resolvedType = null;

            $typeNode = $param->type;

            if ($typeNode instanceof Node\NullableType) {
                $typeNode = $typeNode->type;
                $typeHint = '?';
            }

            if ($typeNode instanceof Node\Name) {
                $localType = NodeHelpers::fetchClassName($typeNode);
                $resolvedType = NodeHelpers::fetchClassName($typeNode->getAttribute('resolvedName'));
                $typeHint .= $resolvedType;
            } elseif ($typeNode instanceof Node\Identifier) {
                $localType = $typeNode->name;
                $resolvedType = $typeNode->name;
                $typeHint .= $resolvedType;
            }

            $isNullable = (
                ($param->type instanceof Node\NullableType) ||
                ($param->default instanceof Node\Expr\ConstFetch && $param->default->name->toString() === 'null')
            );

            $defaultValue = $param->default ?
                substr(
                    $this->code,
                    $param->default->getAttribute('startFilePos'),
                    $param->default->getAttribute('endFilePos') - $param->default->getAttribute('startFilePos') + 1
                ) :
                null;

            $parameterKey = '$' . $param->var->name;
            $parameterDoc = isset($documentation['params'][$parameterKey]) ?
                $documentation['params'][$parameterKey] : null;

            $types = [];

            if ($parameterDoc) {
                $types = $this->getTypeDataForTypeSpecification($parameterDoc['type'], $filePosition);
            } elseif ($localType) {
                $parameterType = $localType;
                $parameterFullType = $resolvedType ?: $parameterType;

                if ($param->variadic) {
                    $parameterType .= '[]';
                    $parameterFullType .= '[]';
                }

                $types = [
                    new Structures\TypeInfo($parameterType, $parameterFullType)
                ];

                if ($isNullable) {
                    $types[] = new Structures\TypeInfo('null', 'null');
                }
            } elseif ($param->default !== null) {
                $typeList = $this->nodeTypeDeducer->deduce($param->default, $this->file, $this->code, 0);

                $types = array_map(function (string $type) {
                    return new Structures\TypeInfo($type, $type);
                }, $typeList);
            }

            $parameter = new Structures\FunctionParameter(
                $function,
                $param->var->name,
                $typeHint,
                $types,
                $parameterDoc ? $parameterDoc['description'] : null,
                $defaultValue,
                $param->byRef,
                !!$param->default,
                $param->variadic
            );

            $this->storage->persist($parameter);
        }
    }

    /**
     * @param string       $typeSpecification
     * @param FilePosition $filePosition
     *
     * @return array[]
     */
    protected function getTypeDataForTypeSpecification(string $typeSpecification, FilePosition $filePosition): array
    {
        $typeList = $this->typeAnalyzer->getTypesForTypeSpecification($typeSpecification);

        return $this->getTypeDataForTypeList($typeList, $filePosition);
    }

    /**
     * @param string[]     $typeList
     * @param FilePosition $filePosition
     *
     * @return Structures\TypeInfo[]
     */
    protected function getTypeDataForTypeList(array $typeList, FilePosition $filePosition): array
    {
        $types = [];

        $positionalNameResolver = $this->structureAwareNameResolverFactory->create($filePosition);

        foreach ($typeList as $type) {
            $types[] = new Structures\TypeInfo($type, $positionalNameResolver->resolve($type, $filePosition));
        }

        return $types;
    }
}
