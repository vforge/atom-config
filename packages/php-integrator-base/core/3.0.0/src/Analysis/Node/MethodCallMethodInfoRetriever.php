<?php

namespace PhpIntegrator\Analysis\Node;

use LogicException;
use UnexpectedValueException;

use PhpIntegrator\Analysis\ClasslikeInfoBuilder;

use PhpIntegrator\Analysis\Typing\Deduction\NodeTypeDeducerInterface;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Fetches method information from a {@see Node\Expr\MethodCall}, {@see Node\Expr\StaticCall} or a {@see Node\Expr\New_}
 * node.
 */
class MethodCallMethodInfoRetriever
{
    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @var ClasslikeInfoBuilder
     */
    private $classlikeInfoBuilder;

    /**
     * @param NodeTypeDeducerInterface $nodeTypeDeducer
     * @param ClasslikeInfoBuilder     $classlikeInfoBuilder
     */
    public function __construct(
        NodeTypeDeducerInterface $nodeTypeDeducer,
        ClasslikeInfoBuilder $classlikeInfoBuilder
    ) {
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        $this->classlikeInfoBuilder = $classlikeInfoBuilder;
    }

    /**
     * @param Node\Expr\MethodCall|Node\Expr\StaticCall|Node\Expr\New_ $node
     * @param Structures\File                                          $file
     * @param string                                                   $code
     * @param int                                                      $offset
     *
     * @throws UnexpectedValueException when a dynamic method call is passed.
     * @throws UnexpectedValueException when the type the method is called on could not be determined.
     *
     * @return array[]
     */
    public function retrieve(Node\Expr $node, Structures\File $file, string $code, int $offset): array
    {
        if (
            !$node instanceof Node\Expr\MethodCall &&
            !$node instanceof Node\Expr\StaticCall &&
            !$node instanceof Node\Expr\New_
        ) {
            throw new LogicException('Expected method call node, got ' . get_class($node) . ' instead');
        } elseif ($node instanceof Node\Expr\New_) {
            if ($node->class instanceof Node\Expr) {
                // Can't currently deduce type of an expression such as "$this->{$foo}()";
                throw new UnexpectedValueException('Can\'t determine information of dynamic method call');
            } elseif ($node->class instanceof Node\Stmt\Class_) {
                throw new UnexpectedValueException('Can\'t determine information of anonymous class constructor call');
            }
        } elseif ($node->name instanceof Node\Expr) {
            // Can't currently deduce type of an expression such as "$this->{$foo}()";
            throw new UnexpectedValueException('Can\'t determine information of dynamic method call');
        }

        $objectNode = ($node instanceof Node\Expr\MethodCall) ? $node->var : $node->class;
        $methodName = ($node instanceof Node\Expr\New_) ? '__construct' : $node->name->name;

        $typesOfVar = $this->nodeTypeDeducer->deduce($objectNode, $file, $code, $offset);

        $infoElements = [];

        foreach ($typesOfVar as $type) {
            $info = null;

            try {
                $info = $this->classlikeInfoBuilder->getClasslikeInfo($type);
            } catch (UnexpectedValueException $e) {
                continue;
            }

            if (!isset($info['methods'][$methodName])) {
                continue;
            }

            $infoElements[] = $info['methods'][$methodName];
        }

        return $infoElements;
    }
}
