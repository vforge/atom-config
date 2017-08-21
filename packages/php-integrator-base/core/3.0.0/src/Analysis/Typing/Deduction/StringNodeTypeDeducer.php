<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Scalar\String_} node.
 */
class StringNodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Scalar\String_) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromStringNode($node);
    }

    /**
     * @param Node\Scalar\String_ $node
     *
     * @return string[]
     */
    protected function deduceTypesFromStringNode(Node\Scalar\String_ $node): array
    {
        return ['string'];
    }
}
