<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Expr\Variable} node.
 */
class VariableNodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @var LocalTypeScanner
     */
    private $localTypeScanner;

    /**
     * @param LocalTypeScanner $localTypeScanner
     */
    public function __construct(LocalTypeScanner $localTypeScanner)
    {
        $this->localTypeScanner = $localTypeScanner;
    }

    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Expr\Variable) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromVariableNode($node, $file, $code, $offset);
    }

    /**
     * @param Node\Expr\Variable $node
     * @param Structures\File    $file
     * @param string             $code
     * @param int                $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromVariableNode(
        Node\Expr\Variable $node,
        Structures\File $file,
        string $code,
        int $offset
    ): array {
        if ($node->name instanceof Node\Expr) {
            return []; // Can't currently deduce type of a variable such as "$$this".
        }

        return $this->localTypeScanner->getLocalExpressionTypes($file, $code, '$' . $node->name, $offset);
    }
}
