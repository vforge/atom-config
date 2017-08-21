<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Stmt\Expression} node.
 */
class ExpressionNodeTypeDeducer extends AbstractNodeTypeDeducer
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
        if (!$node instanceof Node\Stmt\Expression) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromExpressionNode($node, $file, $code, $offset);
    }

    /**
     * @param Node\Stmt\Expression $node
     * @param Structures\File      $file
     * @param string               $code
     * @param int                  $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromExpressionNode(
        Node\Stmt\Expression $node,
        Structures\File $file,
        string $code,
        int $offset
    ): array {
        return $this->nodeTypeDeducer->deduce($node->expr, $file, $code, $offset);
    }
}
