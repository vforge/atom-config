<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Analysis\Node\MethodCallMethodInfoRetriever;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Expr\MethodCall} or a {@see Node\Expr\StaticCall} node.
 */
class MethodCallNodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @var MethodCallMethodInfoRetriever
     */
    private $methodCallMethodInfoRetriever;

    /**
     * @param MethodCallMethodInfoRetriever $methodCallMethodInfoRetriever
     */
    public function __construct(MethodCallMethodInfoRetriever $methodCallMethodInfoRetriever)
    {
        $this->methodCallMethodInfoRetriever = $methodCallMethodInfoRetriever;
    }

    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Expr\MethodCall && !$node instanceof Node\Expr\StaticCall) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromMethodCallNode($node, $file, $code, $offset);
    }

    /**
     * @param Node\Expr\MethodCall|Node\Expr\StaticCall $node
     * @param Structures\File                           $file
     * @param string                                    $code
     * @param int                                       $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromMethodCallNode(
        Node\Expr $node,
        Structures\File $file,
        string $code,
        int $offset
    ): array {
        $infoItems = null;

        try {
            $infoItems = $this->methodCallMethodInfoRetriever->retrieve($node, $file, $code, $offset);
        } catch (UnexpectedValueException $e) {
            return [];
        }

        $types = [];

        foreach ($infoItems as $info) {
            $fetchedTypes = $this->fetchResolvedTypesFromTypeArrays($info['returnTypes']);

            if (!empty($fetchedTypes)) {
                $types += array_combine($fetchedTypes, array_fill(0, count($fetchedTypes), true));
            }
        }

        // We use an associative array so we automatically avoid duplicate types.
        return array_keys($types);
    }
}
