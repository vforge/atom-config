<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Parsing;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Parsing\Node\Keyword\Self_} node.
 */
class SelfNodeTypeDeducer extends AbstractNodeTypeDeducer
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
        if (!$node instanceof Parsing\Node\Keyword\Self_) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromSelf($file, $code, $offset);
    }

    /**
     * @param Structures\File $file
     * @param string          $code
     * @param int             $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromSelf(Structures\File $file, string $code, int $offset): array
    {
        $node = new Node\Name('self');

        return $this->nodeTypeDeducer->deduce($node, $file, $code, $offset);
    }
}
