<?php

namespace PhpIntegrator\Analysis\Visiting;

use PhpParser\Node;

use PhpParser\NodeVisitor\NameResolver;

/**
 * Node visitor that fetches usages of (global) constants.
 */
class GlobalConstantUsageFetchingVisitor extends NameResolver
{
    /**
     * @var Node\Expr\ConstFetch[]
     */
    private $globalConstantList = [];

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

        if (!$node instanceof Node\Expr\ConstFetch) {
            return;
        }

        if (!$this->isConstantExcluded($node->name->toString())) {
            $this->globalConstantList[] = $node;
        }
    }

   /**
    * @param string $name
    *
    * @return bool
    */
   protected function isConstantExcluded(string $name): bool
   {
       return in_array(mb_strtolower($name), ['null', 'true', 'false'], true);
   }

    /**
     * @return Node\Expr\ConstFetch[]
     */
    public function getGlobalConstantList(): array
    {
        return $this->globalConstantList;
    }
}
