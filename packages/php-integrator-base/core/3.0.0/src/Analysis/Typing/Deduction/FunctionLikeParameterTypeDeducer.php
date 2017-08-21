<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\Parsing\DocblockParser;

use PhpIntegrator\Utility\NodeHelpers;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a parameter of a {@see Node\FunctionLike} node.
 */
class FunctionLikeParameterTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var DocblockParser
     */
    private $docblockParser;

    /**
     * @var string|null
     */
    private $functionDocblock;

    /**
     * @param NodeTypeDeducerInterface $nodeTypeDeducer
     * @param TypeAnalyzer             $typeAnalyzer
     * @param DocblockParser           $docblockParser
     */
    public function __construct(
        NodeTypeDeducerInterface $nodeTypeDeducer,
        TypeAnalyzer $typeAnalyzer,
        DocblockParser $docblockParser
    ) {
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        $this->typeAnalyzer = $typeAnalyzer;
        $this->docblockParser = $docblockParser;
    }

    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Param) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromFunctionLikeParameterNode($node, $file, $code, $offset);
    }

    /**
     * @param Node\Param      $node
     * @param Structures\File $file
     * @param string          $code
     * @param int             $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromFunctionLikeParameterNode(
        Node\Param $node,
        Structures\File $file,
        string $code,
        int $offset
    ): array {
        if ($docBlock = $this->getFunctionDocblock()) {
            // Analyze the docblock's @param tags.
            $result = $this->docblockParser->parse((string) $docBlock, [
                DocblockParser::PARAM_TYPE
            ], '', true);

            if (isset($result['params']['$' . $node->var->name])) {
                return $this->typeAnalyzer->getTypesForTypeSpecification($result['params']['$' . $node->var->name]['type']);
            }
        }

        $isNullable = false;
        $typeNode = $node->type;

        if ($typeNode instanceof Node\NullableType) {
            $typeNode = $typeNode->type;
            $isNullable = true;
        } elseif ($node->default instanceof Node\Expr\ConstFetch && $node->default->name->toString() === 'null') {
            $isNullable = true;
        }

        if ($typeNode instanceof Node\Name) {
            $typeHintType = NodeHelpers::fetchClassName($typeNode);

            if ($node->variadic) {
                $typeHintType .= '[]';
            }

            return $isNullable ? [$typeHintType, 'null'] : [$typeHintType];
        } elseif ($node->type instanceof Node\Identifier) {
            return [$node->type->name];
        }

        return [];
    }

    /**
     * @return string|null
     */
    public function getFunctionDocblock(): ?string
    {
        return $this->functionDocblock;
    }

    /**
     * @param string|null $functionDocblock
     *
     * @return static
     */
    public function setFunctionDocblock(?string $functionDocblock)
    {
        $this->functionDocblock = $functionDocblock;
        return $this;
    }
}
