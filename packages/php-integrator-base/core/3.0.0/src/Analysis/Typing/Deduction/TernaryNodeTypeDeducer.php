<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Expr\Ternary} node.
 */
class TernaryNodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @param NodeTypeDeducerInterface $nodeTypeDeducer
     */
    public function __construct(NodeTypeDeducerInterface $nodeTypeDeducer)
    {
        $this->nodeTypeDeducer = $nodeTypeDeducer;
    }

    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Expr\Ternary) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromTernaryNode($node, $file, $code, $offset);
    }

    /**
     * @param Node\Expr\Ternary $node
     * @param Structures\File   $file
     * @param string            $code
     * @param int               $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromTernaryNode(
        Node\Expr\Ternary $node,
        Structures\File $file,
        string $code,
        int $offset
    ): array {
        $firstOperandTypes = $this->nodeTypeDeducer->deduce(
            $node->if ?: $node->cond,
            $file,
            $code,
            $node->getAttribute('startFilePos')
        );

        $secondOperandTypes = $this->nodeTypeDeducer->deduce(
            $node->else,
            $file,
            $code,
            $node->getAttribute('startFilePos')
        );

        return array_unique(array_merge($firstOperandTypes, $secondOperandTypes));
    }
}
