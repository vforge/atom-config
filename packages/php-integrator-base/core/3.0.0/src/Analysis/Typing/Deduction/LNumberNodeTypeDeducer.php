<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Scalar\LNumber} node.
 */
class LNumberNodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Scalar\LNumber) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromLNumberNode($node);
    }

    /**
     * @param Node\Scalar\LNumber $node
     *
     * @return string[]
     */
    protected function deduceTypesFromLNumberNode(Node\Scalar\LNumber $node): array
    {
        return ['int'];
    }
}
