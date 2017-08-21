<?php

namespace PhpIntegrator\Analysis;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * Visitor that queries the nodes for information about available (set) variables.
 */
class VariableScanningVisitor extends NodeVisitorAbstract
{
    /**
     * @var string[]
     */
    private $variables = [];

    /**
     * @var int
     */
    private $position;

    /**
     * @var bool
     */
    private $hasThisContext;

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
        if ($node->getAttribute('startFilePos') >= $this->position) {
            // We've gone beyond the requested position, there is nothing here that can still be relevant anymore.
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        if ($node->getAttribute('startFilePos') <= $this->position &&
            $node->getAttribute('endFilePos') >= $this->position
        ) {
            if ($node instanceof Node\Stmt\ClassLike) {
                $this->hasThisContext = true;

                // We've entered a new scope, variables that we previously picked up are outside of it and not available
                // here.
                $this->variables = [];
            } elseif ($node instanceof Node\FunctionLike) {
                if ($node instanceof Node\Expr\Closure) {
                    // Closures can have a custom object bound to the $this variable. There is no way for us to detect
                    // whether this actually happened (as that is only known at runtime), so just include the variable.
                    $this->hasThisContext = true;
                }

                $this->variables = [];
            }
        }

        if ($node instanceof Node\Expr\Variable) {
            if ($node->getAttribute('endFilePos') < $this->position) {
                $this->parseVariable($node);
            }
        } elseif ($node instanceof Node\Expr\ClosureUse) {
            $this->parseClosureUse($node);
        } elseif ($node instanceof Node\Param) {
            $this->parseParam($node);
        }
    }

    /**
     * @param Node\Expr\Variable $node
     *
     * @return void
     */
    protected function parseVariable(Node\Expr\Variable $node): void
    {
        if (is_string($node->name)) {
            $this->variables[] = '$' . $node->name;
        }
    }

    /**
     * @param Node\Expr\ClosureUse $node
     *
     * @return void
     */
    protected function parseClosureUse(Node\Expr\ClosureUse $node): void
    {
        $this->variables[] = '$' . $node->var->name;
    }

    /**
     * @param Node\Param $node
     *
     * @return void
     */
    protected function parseParam(Node\Param $node): void
    {
        $this->variables[] = '$' . $node->var->name;
    }

    /**
     * Retrieves the detected variables.
     *
     * @return string[]
     */
    public function getVariables(): array
    {
        $variables = $this->variables;

        if ($this->hasThisContext) {
            $variables[] = '$this';
        }

        return $variables;
    }

    /**
     * Retrieves the detected variables, sorted by their proximity to the configured location. Note that $this will
     * still be listed first as it's always closest in the sense that it's always available.
     *
     * @return string[]
     */
    public function getVariablesSortedByProximity(): array
    {
        $variables = array_reverse($this->variables);

        if ($this->hasThisContext) {
            array_unshift($variables, '$this');
        }

        return $variables;
    }
}
