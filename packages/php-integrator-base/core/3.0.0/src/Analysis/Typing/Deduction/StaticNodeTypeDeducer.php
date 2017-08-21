<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Parsing;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Parsing\Node\Keyword\Static_} node.
 */
class StaticNodeTypeDeducer extends AbstractNodeTypeDeducer
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
        if (!$node instanceof Parsing\Node\Keyword\Static_) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromStatic($file, $code, $offset);
    }

    /**
     * @param Structures\File $file
     * @param string          $code
     * @param int             $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromStatic(Structures\File $file, string $code, int $offset): array
    {
        $node = new Node\Name('static');

        return $this->nodeTypeDeducer->deduce($node, $file, $code, $offset);
    }
}
