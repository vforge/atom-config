<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Stmt\Catch_} node.
 */
class CatchNodeTypeDeducer extends AbstractNodeTypeDeducer
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
        if (!$node instanceof Node\Stmt\Catch_) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromCatchNode($node, $file, $code, $offset);
    }

    /**
     * @param Node\Stmt\Catch_ $node
     * @param Structures\File  $file
     * @param string           $code
     * @param int              $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromCatchNode(
        Node\Stmt\Catch_ $node,
        Structures\File $file,
        string $code,
        int $offset
    ): array {
        $types = array_map(function (Node\Name $name) use ($file, $code, $offset) {
            return $this->nodeTypeDeducer->deduce($name, $file, $code, $offset);
        }, $node->types);

        $types = array_reduce($types, function (array $subTypes, $carry) {
            return array_merge($carry, $subTypes);
        }, []);

        return $types;
    }
}
