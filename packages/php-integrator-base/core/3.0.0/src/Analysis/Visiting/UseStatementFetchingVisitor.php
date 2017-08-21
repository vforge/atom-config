<?php

namespace PhpIntegrator\Analysis\Visiting;

use DomainException;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Node visitor that fetches namespaces and their use statements.
 */
class UseStatementFetchingVisitor extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    private $namespaces = [];

    /**
     * @var int
     */
    private $lastNamespaceIndex = 0;

    /**
     * @var int
     */
    private $lastLine;

    /**
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->lastLine = mb_substr_count($code, "\n");

        $this->namespaces[0] = [
            'name'          => null,
            'startLine'     => 0,
            'endLine'       => $this->lastLine + 1,
            'useStatements' => []
        ];

        $this->lastNamespaceIndex = 0;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            // There is no way to fetch the end of a namespace, so determine it manually (a value of null signifies the
            // end of the file).
            $this->beginNamespace($node);
        } elseif ($node instanceof Node\Stmt\Use_ || $node instanceof Node\Stmt\GroupUse) {
            $this->registerImportNode($node);
        }

        // if (isset($this->namespaces[$this->lastNamespaceIndex])) {
        //     $this->namespaces[$this->lastNamespaceIndex]['endLine'] = max(
        //         $this->namespaces[$this->lastNamespaceIndex]['endLine'],
        //         $node->getAttribute('endLine') + 1
        //     );
        // }
    }

    /**
     * @inheritDoc
     */
    public function afterTraverse(array $nodes)
    {
        if (isset($this->namespaces[$this->lastNamespaceIndex])) {
            $this->namespaces[$this->lastNamespaceIndex]['endLine'] = $this->lastLine + 1;
        }
    }

    /**
     * Retrieves a list of namespaces.
     *
     * @return array[]
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * @param Node\Stmt\Namespace_ $node
     */
    protected function beginNamespace(Node\Stmt\Namespace_ $node): void
    {
        $this->namespaces[$this->lastNamespaceIndex]['endLine'] = $node->getLine() - 1;
        $this->namespaces[++$this->lastNamespaceIndex] = [
            'name'          => $node->name ? (string) $node->name : null,
            'startLine'     => $node->getLine(),
            'endLine'       => $node->getLine() + 1,
            'useStatements' => []
        ];
    }

    /**
     * @param Node\Stmt\Use_|Node\Stmt\GroupUse $node
     *
     * @return void
     */
    protected function registerImportNode(Node $node): void
    {
        $prefix = '';

        if ($node instanceof Node\Stmt\GroupUse) {
            $prefix = ((string) $node->prefix) . '\\';
        };

        foreach ($node->uses as $use) {
            $this->registerImport($node, $use, $prefix);
        }
    }

    /**
     * @param Node\Stmt\Use_|Node\Stmt\GroupUse $node
     * @param Node\Stmt\UseUse                  $use
     * @param string                            $prefix
     *
     * @return void
     */
    protected function registerImport(Node $node, Node\Stmt\UseUse $use, string $prefix): void
    {
        $type = $node->type === Node\Stmt\Use_::TYPE_UNKNOWN ? $use->type : $node->type;

        if ($type === Node\Stmt\Use_::TYPE_UNKNOWN) {
            throw new DomainException('Unknown use statement type encountered!');
        }

        $kindMap = [
            Node\Stmt\Use_::TYPE_NORMAL   => UseStatementKind::TYPE_CLASSLIKE,
            Node\Stmt\Use_::TYPE_FUNCTION => UseStatementKind::TYPE_FUNCTION,
            Node\Stmt\Use_::TYPE_CONSTANT => UseStatementKind::TYPE_CONSTANT
        ];

        $this->namespaces[$this->lastNamespaceIndex]['useStatements'][$use->getAlias()->name] = [
            'name'  => $prefix . ((string) $use->name),
            'alias' => $use->getAlias()->name,
            'kind'  => $kindMap[$type],
            'line'  => $node->getLine(),
            'start' => $use->getAttribute('startFilePos') ? $use->getAttribute('startFilePos')   : null,
            'end'   => $use->getAttribute('endFilePos')   ? $use->getAttribute('endFilePos') + 1 : null
        ];
    }
}
