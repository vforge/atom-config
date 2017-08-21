<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use DomainException;

use PhpIntegrator\Parsing;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node} object.
 *
 * This is a thin type deducer that can deduce the type of any node by delegating the type deduction to a more
 * appropriate deducer returned by the configured factory.
 */
class NodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @var NodeTypeDeducerInterface
     */
    private $variableNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $lNumberNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $dNumberNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $stringNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $constFetchNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $arrayDimFetchNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $closureNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $newNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $cloneNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $arrayNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $selfNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $staticNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $parentNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $nameNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $funcCallNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $methodCallNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $propertyFetchNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $classConstFetchNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $assignNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $ternaryNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $classLikeNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $catchNodeTypeDeducer;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $expressionNodeTypeDeducer;

    /**
     * @param NodeTypeDeducerInterface $variableNodeTypeDeducer
     * @param NodeTypeDeducerInterface $lNumberNodeTypeDeducer
     * @param NodeTypeDeducerInterface $dNumberNodeTypeDeducer
     * @param NodeTypeDeducerInterface $stringNodeTypeDeducer
     * @param NodeTypeDeducerInterface $constFetchNodeTypeDeducer
     * @param NodeTypeDeducerInterface $arrayDimFetchNodeTypeDeducer
     * @param NodeTypeDeducerInterface $closureNodeTypeDeducer
     * @param NodeTypeDeducerInterface $newNodeTypeDeducer
     * @param NodeTypeDeducerInterface $cloneNodeTypeDeducer
     * @param NodeTypeDeducerInterface $arrayNodeTypeDeducer
     * @param NodeTypeDeducerInterface $selfNodeTypeDeducer
     * @param NodeTypeDeducerInterface $staticNodeTypeDeducer
     * @param NodeTypeDeducerInterface $parentNodeTypeDeducer
     * @param NodeTypeDeducerInterface $nameNodeTypeDeducer
     * @param NodeTypeDeducerInterface $funcCallNodeTypeDeducer
     * @param NodeTypeDeducerInterface $methodCallNodeTypeDeducer
     * @param NodeTypeDeducerInterface $propertyFetchNodeTypeDeducer
     * @param NodeTypeDeducerInterface $classConstFetchNodeTypeDeducer
     * @param NodeTypeDeducerInterface $assignNodeTypeDeducer
     * @param NodeTypeDeducerInterface $ternaryNodeTypeDeducer
     * @param NodeTypeDeducerInterface $classLikeNodeTypeDeducer
     * @param NodeTypeDeducerInterface $catchNodeTypeDeducer
     * @param NodeTypeDeducerInterface $expressionNodeTypeDeducer
     */
    public function __construct(
        NodeTypeDeducerInterface $variableNodeTypeDeducer,
        NodeTypeDeducerInterface $lNumberNodeTypeDeducer,
        NodeTypeDeducerInterface $dNumberNodeTypeDeducer,
        NodeTypeDeducerInterface $stringNodeTypeDeducer,
        NodeTypeDeducerInterface $constFetchNodeTypeDeducer,
        NodeTypeDeducerInterface $arrayDimFetchNodeTypeDeducer,
        NodeTypeDeducerInterface $closureNodeTypeDeducer,
        NodeTypeDeducerInterface $newNodeTypeDeducer,
        NodeTypeDeducerInterface $cloneNodeTypeDeducer,
        NodeTypeDeducerInterface $arrayNodeTypeDeducer,
        NodeTypeDeducerInterface $selfNodeTypeDeducer,
        NodeTypeDeducerInterface $staticNodeTypeDeducer,
        NodeTypeDeducerInterface $parentNodeTypeDeducer,
        NodeTypeDeducerInterface $nameNodeTypeDeducer,
        NodeTypeDeducerInterface $funcCallNodeTypeDeducer,
        NodeTypeDeducerInterface $methodCallNodeTypeDeducer,
        NodeTypeDeducerInterface $propertyFetchNodeTypeDeducer,
        NodeTypeDeducerInterface $classConstFetchNodeTypeDeducer,
        NodeTypeDeducerInterface $assignNodeTypeDeducer,
        NodeTypeDeducerInterface $ternaryNodeTypeDeducer,
        NodeTypeDeducerInterface $classLikeNodeTypeDeducer,
        NodeTypeDeducerInterface $catchNodeTypeDeducer,
        NodeTypeDeducerInterface $expressionNodeTypeDeducer
    ) {
        $this->variableNodeTypeDeducer = $variableNodeTypeDeducer;
        $this->lNumberNodeTypeDeducer = $lNumberNodeTypeDeducer;
        $this->dNumberNodeTypeDeducer = $dNumberNodeTypeDeducer;
        $this->stringNodeTypeDeducer = $stringNodeTypeDeducer;
        $this->constFetchNodeTypeDeducer = $constFetchNodeTypeDeducer;
        $this->arrayDimFetchNodeTypeDeducer = $arrayDimFetchNodeTypeDeducer;
        $this->closureNodeTypeDeducer = $closureNodeTypeDeducer;
        $this->newNodeTypeDeducer = $newNodeTypeDeducer;
        $this->cloneNodeTypeDeducer = $cloneNodeTypeDeducer;
        $this->arrayNodeTypeDeducer = $arrayNodeTypeDeducer;
        $this->selfNodeTypeDeducer = $selfNodeTypeDeducer;
        $this->staticNodeTypeDeducer = $staticNodeTypeDeducer;
        $this->parentNodeTypeDeducer = $parentNodeTypeDeducer;
        $this->nameNodeTypeDeducer = $nameNodeTypeDeducer;
        $this->funcCallNodeTypeDeducer = $funcCallNodeTypeDeducer;
        $this->methodCallNodeTypeDeducer = $methodCallNodeTypeDeducer;
        $this->propertyFetchNodeTypeDeducer = $propertyFetchNodeTypeDeducer;
        $this->classConstFetchNodeTypeDeducer = $classConstFetchNodeTypeDeducer;
        $this->assignNodeTypeDeducer = $assignNodeTypeDeducer;
        $this->ternaryNodeTypeDeducer = $ternaryNodeTypeDeducer;
        $this->classLikeNodeTypeDeducer = $classLikeNodeTypeDeducer;
        $this->catchNodeTypeDeducer = $catchNodeTypeDeducer;
        $this->expressionNodeTypeDeducer = $expressionNodeTypeDeducer;
    }

    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        $typeDeducer = null;

        try {
            $typeDeducer = $this->getTypeDeducerForNode($node);
        } catch (DomainException $e) {
            return [];
        }

        return $typeDeducer->deduce($node, $file, $code, $offset);
    }

    /**
     * @param Node $node
     *
     * @throws DomainException
     *
     * @return NodeTypeDeducerInterface
     */
    protected function getTypeDeducerForNode(Node $node): NodeTypeDeducerInterface
    {
        return $this->getTypeDeducerForNodeClass(get_class($node));
    }

    /**
     * @param string $class
     *
     * @throws DomainException
     *
     * @return NodeTypeDeducerInterface
     */
    protected function getTypeDeducerForNodeClass(string $class): NodeTypeDeducerInterface
    {
        $map = [
            Node\Expr\Variable::class            => $this->variableNodeTypeDeducer,
            Node\Scalar\LNumber::class           => $this->lNumberNodeTypeDeducer,
            Node\Scalar\DNumber::class           => $this->dNumberNodeTypeDeducer,
            Node\Scalar\String_::class           => $this->stringNodeTypeDeducer,
            Node\Expr\ConstFetch::class          => $this->constFetchNodeTypeDeducer,
            Node\Expr\ArrayDimFetch::class       => $this->arrayDimFetchNodeTypeDeducer,
            Node\Expr\Closure::class             => $this->closureNodeTypeDeducer,
            Node\Expr\New_::class                => $this->newNodeTypeDeducer,
            Node\Expr\Clone_::class              => $this->cloneNodeTypeDeducer,
            Node\Expr\Array_::class              => $this->arrayNodeTypeDeducer,
            Parsing\Node\Keyword\Self_::class    => $this->selfNodeTypeDeducer,
            Parsing\Node\Keyword\Static_::class  => $this->staticNodeTypeDeducer,
            Parsing\Node\Keyword\Parent_::class  => $this->parentNodeTypeDeducer,
            Node\Name::class                     => $this->nameNodeTypeDeducer,
            Node\Name\FullyQualified::class      => $this->nameNodeTypeDeducer,
            Node\Name\Relative::class            => $this->nameNodeTypeDeducer,
            Node\Expr\FuncCall::class            => $this->funcCallNodeTypeDeducer,
            Node\Expr\MethodCall::class          => $this->methodCallNodeTypeDeducer,
            Node\Expr\StaticCall::class          => $this->methodCallNodeTypeDeducer,
            Node\Expr\PropertyFetch::class       => $this->propertyFetchNodeTypeDeducer,
            Node\Expr\StaticPropertyFetch::class => $this->propertyFetchNodeTypeDeducer,
            Node\Expr\ClassConstFetch::class     => $this->classConstFetchNodeTypeDeducer,
            Node\Expr\Assign::class              => $this->assignNodeTypeDeducer,
            Node\Expr\Ternary::class             => $this->ternaryNodeTypeDeducer,
            Node\Stmt\Class_::class              => $this->classLikeNodeTypeDeducer,
            Node\Stmt\Interface_::class          => $this->classLikeNodeTypeDeducer,
            Node\Stmt\Trait_::class              => $this->classLikeNodeTypeDeducer,
            Node\Stmt\Catch_::class              => $this->catchNodeTypeDeducer,
            Node\Stmt\Expression::class          => $this->expressionNodeTypeDeducer
        ];

        if (!isset($map[$class])) {
            throw new DomainException("No deducer known for class {$class}");
        }

        return $map[$class];
    }
}
