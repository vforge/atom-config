<?php

namespace PhpIntegrator\Analysis\Visiting;

use PhpParser\Node;

use PhpParser\NodeVisitor\NameResolver;

/**
 * Visitor that attaches the active namespace to each node it traverses.
 */
class NamespaceAttachingVisitor extends NameResolver
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(null, [
            'replaceNodes' => false
        ]);
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        parent::enterNode($node);

        $node->setAttribute('namespace', $this->nameContext->getNamespace());
    }
}
