<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Expr\Assign} node.
 */
class AssignNodeTypeDeducer extends AbstractNodeTypeDeducer
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
        if (!$node instanceof Node\Expr\Assign) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromAssignNode($node, $file, $code, $offset);
    }

    /**
     * @param Node\Expr\Assign $node
     * @param Structures\File  $file
     * @param string           $code
     * @param int              $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromAssignNode(
        Node\Expr\Assign $node,
        Structures\File $file,
        string $code,
        int $offset
    ): array {
        return $this->nodeTypeDeducer->deduce($node->expr, $file, $code, $node->getAttribute('startFilePos'));
    }
}
