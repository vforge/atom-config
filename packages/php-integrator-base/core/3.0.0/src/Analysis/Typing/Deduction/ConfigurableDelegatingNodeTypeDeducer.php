<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node} object by delegating it to another (configurable) object.
 */
class ConfigurableDelegatingNodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @var NodeTypeDeducerInterface|null
     */
    private $nodeTypeDeducer;

    /**
     * @param NodeTypeDeducerInterface|null $nodeTypeDeducer
     */
    public function __construct(NodeTypeDeducerInterface $nodeTypeDeducer = null)
    {
        $this->nodeTypeDeducer = $nodeTypeDeducer;
    }

    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        if (!$this->nodeTypeDeducer) {
            throw new UnexpectedValueException('No node type deducer to delegate to configured!');
        }

        return $this->nodeTypeDeducer->deduce($node, $file, $code, $offset);
    }

    /**
     * @return NodeTypeDeducerInterface|null
     */
    public function getNodeTypeDeducer(): ?NodeTypeDeducerInterface
    {
        return $this->nodeTypeDeducer;
    }

    /**
     * @param NodeTypeDeducerInterface|null $nodeTypeDeducer
     *
     * @return static
     */
    public function setNodeTypeDeducer(NodeTypeDeducerInterface $nodeTypeDeducer = null)
    {
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        return $this;
    }
}
