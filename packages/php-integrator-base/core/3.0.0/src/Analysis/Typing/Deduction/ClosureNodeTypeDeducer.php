<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Expr\Closure} node.
 */
class ClosureNodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Expr\Closure) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromClosureNode($node);
    }

    /**
     * @param Node\Expr\Closure $node
     *
     * @return string[]
     */
    protected function deduceTypesFromClosureNode(Node\Expr\Closure $node): array
    {
        return ['\Closure'];
    }
}
