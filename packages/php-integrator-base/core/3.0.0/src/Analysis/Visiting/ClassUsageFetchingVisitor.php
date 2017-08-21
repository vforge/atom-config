<?php

namespace PhpIntegrator\Analysis\Visiting;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

use PhpIntegrator\Utility\NodeHelpers;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * Node visitor that fetches usages of class, trait, and interface names.
 */
class ClassUsageFetchingVisitor extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    private $classUsageList = [];

    /**
     * @var Node|null
     */
    private $lastNode;

    /**
     * @var TypeAnalyzer|null
     */
    private $typeAnalyzer = null;

    /**
     * @var string|null
     */
    private $lastNamespace = null;

    /**
     * @param TypeAnalyzer $typeAnalyzer
     */
    public function __construct(TypeAnalyzer $typeAnalyzer)
    {
        $this->typeAnalyzer = $typeAnalyzer;

        $this->lastNamespace = null;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->lastNamespace = (string) $node->name;
        }

        if ($node instanceof Node\Stmt\Use_ || $node instanceof Node\Stmt\GroupUse) {
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof Node\Name) {
            $this->processName($node);
        } elseif ($node instanceof Node\Stmt\Class_ && $node->name === null) {
            // NOTE: Extends and implements for classlikes are automatically traversed, but this does not happen for
            // anonymous classes, which is handled separately here.
            if ($node->extends instanceof Node\Name) {
                $this->processName($node->extends);
            }

            foreach ($node->implements as $implements) {
                $this->processName($implements);
            }
        }

        $this->lastNode = $node;
    }

    /**
     * @param Node\Name $node
     *
     * @return void
     */
    protected function processName(Node\Name $node): void
    {
        if ($this->lastNode instanceof Node\Expr\FuncCall ||
            $this->lastNode instanceof Node\Expr\ConstFetch ||
            $this->lastNode instanceof Node\Stmt\Namespace_
        ) {
            return;
        } elseif (!$this->isValidNameNode($node)) {
            return;
        }

        $this->classUsageList[] = [
            'name'             => (string) $node,
            'firstPart'        => $node->getFirst(),
            'isFullyQualified' => $node->isFullyQualified(),
            'namespace'        => $this->lastNamespace,
            'line'             => $node->getAttribute('startLine')    ? $node->getAttribute('startLine')      : null,
            'start'            => $node->getAttribute('startFilePos') ? $node->getAttribute('startFilePos')   : null,
            'end'              => $node->getAttribute('endFilePos')   ? $node->getAttribute('endFilePos') + 1 : null
        ];
    }

    /**
     * @param Node\Name $node
     *
     * @return bool
     */
     protected function isValidNameNode(Node\Name $node): bool
     {
         return !NodeHelpers::isReservedNameNode($node);
     }

    /**
     * Retrieves the class usage list.
     *
     * @return array
     */
    public function getClassUsageList(): array
    {
        return $this->classUsageList;
    }
}
