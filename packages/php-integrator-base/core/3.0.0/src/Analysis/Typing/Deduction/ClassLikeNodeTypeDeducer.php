<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Stmt\ClassLike} node.
 */
class ClassLikeNodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Stmt\ClassLike) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromClassLikeNode($node);
    }

    /**
     * @param Node\Stmt\ClassLike $node
     *
     * @return string[]
     */
    protected function deduceTypesFromClassLikeNode(Node\Stmt\ClassLike $node): array
    {
        if ($node->name === null) {
            return [];
        }

        return [(string) $node->name];
    }
}
