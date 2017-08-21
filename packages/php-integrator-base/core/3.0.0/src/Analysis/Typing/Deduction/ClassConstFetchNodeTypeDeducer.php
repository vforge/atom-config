<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Analysis\ClasslikeInfoBuilder;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Expr\ClassConstFetch} node.
 */
class ClassConstFetchNodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @var ClasslikeInfoBuilder
     */
    private $classlikeInfoBuilder;

    /**
     * @param NodeTypeDeducerInterface $nodeTypeDeducer
     * @param ClasslikeInfoBuilder            $classlikeInfoBuilder
     */
    public function __construct(
        NodeTypeDeducerInterface $nodeTypeDeducer,
        ClasslikeInfoBuilder $classlikeInfoBuilder
    ) {
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        $this->classlikeInfoBuilder = $classlikeInfoBuilder;
    }

    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Expr\ClassConstFetch) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromClassConstFetchNode($node, $file, $code, $offset);
    }

    /**
     * @param Node\Expr\ClassConstFetch $node
     * @param Structures\File           $file
     * @param string                    $code
     * @param int                       $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromClassConstFetchNode(
        Node\Expr\ClassConstFetch $node,
        Structures\File $file,
        string $code,
        int $offset
    ): array {
        $typesOfVar = $this->nodeTypeDeducer->deduce($node->class, $file, $code, $offset);

        $types = [];

        foreach ($typesOfVar as $type) {
            $info = null;

            try {
                $info = $this->classlikeInfoBuilder->getClasslikeInfo($type);
            } catch (UnexpectedValueException $e) {
                continue;
            }

            if (isset($info['constants'][$node->name->name])) {
                $fetchedTypes = $this->fetchResolvedTypesFromTypeArrays($info['constants'][$node->name->name]['types']);

                if (!empty($fetchedTypes)) {
                    $types += array_combine($fetchedTypes, array_fill(0, count($fetchedTypes), true));
                }
            }
        }

        // We use an associative array so we automatically avoid duplicate types.
        return array_keys($types);
    }
}
