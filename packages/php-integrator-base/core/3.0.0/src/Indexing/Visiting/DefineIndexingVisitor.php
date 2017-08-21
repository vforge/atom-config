<?php

namespace PhpIntegrator\Indexing\Visiting;

use PhpIntegrator\Analysis\Typing\Deduction\NodeTypeDeducerInterface;

use PhpIntegrator\Indexing\Structures;
use PhpIntegrator\Indexing\StorageInterface;

use PhpIntegrator\Utility\NodeHelpers;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Visitor that traverses a set of nodes, indexing defines in the process.
 */
final class DefineIndexingVisitor extends NodeVisitorAbstract
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @var Structures\File
     */
    private $file;

    /**
     * @var string
     */
    private $code;

    /**
     * @param StorageInterface         $storage
     * @param NodeTypeDeducerInterface $nodeTypeDeducer
     * @param Structures\File          $file
     * @param string                   $code
     */
    public function __construct(
        StorageInterface $storage,
        NodeTypeDeducerInterface $nodeTypeDeducer,
        Structures\File $file,
        string $code
    ) {
        $this->storage = $storage;
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        $this->file = $file;
        $this->code = $code;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if (
            $node instanceof Node\Expr\FuncCall &&
            $node->name instanceof Node\Name &&
            $node->name->toString() === 'define'
        ) {
            $this->parseDefineNode($node);
        }
    }

    /**
     * @param Node\Expr\FuncCall $node
     *
     * @return void
     */
    protected function parseDefineNode(Node\Expr\FuncCall $node): void
    {
        if (count($node->args) < 2) {
            return;
        }

        $nameValue = $node->args[0]->value;

        if (!$nameValue instanceof Node\Scalar\String_) {
            return;
        }

        // Defines can be namespaced if their name contains slashes, see also
        // https://php.net/manual/en/function.define.php#90282
        $name = new Node\Name((string) $nameValue->value);

        $types = [];

        $defaultValue = substr(
            $this->code,
            $node->args[1]->getAttribute('startFilePos'),
            $node->args[1]->getAttribute('endFilePos') - $node->args[1]->getAttribute('startFilePos') + 1
        );

        if ($node->args[1]) {
            $typeList = $this->nodeTypeDeducer->deduce($node->args[1]->value, $this->file, $this->code, 0);

            $types = array_map(function (string $type) {
                return new Structures\TypeInfo($type, $type);
            }, $typeList);
        }

        $constant = new Structures\Constant(
            $name->getLast(),
            '\\' . NodeHelpers::fetchClassName($name),
            $this->file,
            $node->getLine(),
            $node->getAttribute('endLine'),
            $defaultValue,
            false,
            false,
            null,
            null,
            null,
            $types
        );

        $this->storage->persist($constant);
    }
}
