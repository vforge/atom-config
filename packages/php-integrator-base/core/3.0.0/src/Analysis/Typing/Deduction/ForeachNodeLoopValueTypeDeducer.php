<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of the loop value of a {@see Node\Stmt\Foreach_} node.
 */
class ForeachNodeLoopValueTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @param NodeTypeDeducerInterface $nodeTypeDeducer
     * @param TypeAnalyzer             $typeAnalyzer
     */
    public function __construct(NodeTypeDeducerInterface $nodeTypeDeducer, TypeAnalyzer $typeAnalyzer)
    {
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        $this->typeAnalyzer = $typeAnalyzer;
    }

    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Stmt\Foreach_) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromForeachNode($node, $file, $code, $offset);
    }

    /**
     * @param Node\Stmt\Foreach_ $node
     * @param Structures\File    $file
     * @param string             $code
     * @param int                $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromForeachNode(
        Node\Stmt\Foreach_ $node,
        Structures\File $file,
        string $code,
        int $offset
    ): array {
        $types = $this->nodeTypeDeducer->deduce($node->expr, $file, $code, $node->getAttribute('startFilePos'));

        foreach ($types as $type) {
            if ($this->typeAnalyzer->isArraySyntaxTypeHint($type)) {
                return [$this->typeAnalyzer->getValueTypeFromArraySyntaxTypeHint($type)];
            }
        }

        return [];
    }
}
