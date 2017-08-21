<?php

namespace PhpIntegrator\Analysis\Visiting;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * Visitor that retrieves the node at a specific location.
 */
class NodeFetchingVisitor extends NodeVisitorAbstract
{
    /**
     * @var int
     */
    private $position;

    /**
     * @var Node
     */
    private $matchingNode;

    /**
     * @var Node
     */
    private $mostInterestingNode;

    /**
     * Constructor.
     *
     * @param int $position
     */
    public function __construct(int $position)
    {
        $this->position = $position;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        $endFilePos = $node->getAttribute('endFilePos');
        $startFilePos = $node->getAttribute('startFilePos');

        if ($startFilePos > $this->position || $endFilePos < $this->position) {
            return;
        }

        $this->matchingNode = $node;

        if (!$node instanceof Node\Name && !$node instanceof Node\Identifier) {
            $this->mostInterestingNode = $node;
        }
    }

    /**
     * @return Node|null
     */
    public function getNode(): ?Node
    {
        return $this->matchingNode;
    }

    /**
     * Returns the same as {@see getNode}, or the nearest node that is more interesting.
     *
     * "More interesting" is defined in terms of what is more useful. {@see getNode} may return the name node inside a
     * function call, whilst this method will return the function call instead.
     *
     * @return Node|null
     */
    public function getNearestInterestingNode(): ?Node
    {
        return $this->mostInterestingNode;
    }
}
