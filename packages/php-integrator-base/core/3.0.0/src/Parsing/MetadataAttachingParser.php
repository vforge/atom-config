<?php

namespace PhpIntegrator\Parsing;

use PhpIntegrator\Analysis\Visiting\ParentAttachingVisitor;
use PhpIntegrator\Analysis\Visiting\NamespaceAttachingVisitor;

use PhpParser\Parser;
use PhpParser\ErrorHandler;
use PhpParser\NodeTraverser;

use PhpParser\NodeVisitor\NameResolver;

/**
 * Parser that delegates parsing to another parser and attaches metadata to the nodes.
 */
class MetadataAttachingParser implements Parser
{
    /**
     * @var Parser
     */
    private $delegate;

    /**
     * @param Parser $delegate
     */
    public function __construct(Parser $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * @inheritDoc
     */
    public function parse(string $code, ErrorHandler $errorHandler = null)
    {
        $nodes = $this->delegate->parse($code, $errorHandler);

        if ($nodes === null) {
            return $nodes;
        }

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new NameResolver(null, [
            'replaceNodes' => false
        ]));

        $traverser->addVisitor(new NamespaceAttachingVisitor());
        $traverser->addVisitor(new ParentAttachingVisitor());

        $traverser->traverse($nodes);

        return $nodes;
    }
}
