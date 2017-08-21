<?php

namespace PhpIntegrator\Analysis\Visiting;

use UnexpectedValueException;

use PhpIntegrator\Analysis\ClasslikeInfoBuilder;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

use PhpIntegrator\Analysis\Typing\Deduction\NodeTypeDeducerInterface;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Node visitor that fetches usages of member names.
 */
class MemberUsageFetchingVisitor extends NodeVisitorAbstract
{
    /**
     * @var int
     */
    public const TYPE_EXPRESSION_HAS_NO_TYPE = 1;

    /**
     * @var int
     */
    public const TYPE_EXPRESSION_HAS_NO_SUCH_MEMBER = 2;

    /**
     * @var int
     */
    public const TYPE_EXPRESSION_NEW_MEMBER_WILL_BE_CREATED = 4;

    /**
     * @var array
     */
    private $memberCallList = [];

    /**
     * @var Node|null
     */
    private $lastNode = null;

    /**
     * @var Structures\File
     */
    private $file;

    /**
     * @var string
     */
    private $code;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var ClasslikeInfoBuilder
     */
    private $classlikeInfoBuilder;

    /**
     * @param NodeTypeDeducerInterface $nodeTypeDeducer
     * @param ClasslikeInfoBuilder     $classlikeInfoBuilder
     * @param TypeAnalyzer             $typeAnalyzer
     * @param Structures\File          $file
     * @param string                   $code
     */
    public function __construct(
        NodeTypeDeducerInterface $nodeTypeDeducer,
        ClasslikeInfoBuilder $classlikeInfoBuilder,
        TypeAnalyzer $typeAnalyzer,
        Structures\File $file,
        string $code
    ) {
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        $this->classlikeInfoBuilder = $classlikeInfoBuilder;
        $this->typeAnalyzer = $typeAnalyzer;
        $this->file = $file;
        $this->code = $code;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        $previousNode = $this->lastNode;
        $this->lastNode = $node;

        if (!$node instanceof Node\Expr\MethodCall &&
            !$node instanceof Node\Expr\StaticCall &&
            !$node instanceof Node\Expr\PropertyFetch &&
            !$node instanceof Node\Expr\StaticPropertyFetch &&
            !$node instanceof Node\Expr\ClassConstFetch
        ) {
            return;
        }

        $objectTypes = [];
        $nodeToDeduceTypeFrom = null;

        if ($node instanceof Node\Expr\MethodCall || $node instanceof Node\Expr\PropertyFetch) {
            $nodeToDeduceTypeFrom = $node->var;
        } elseif (
            $node instanceof Node\Expr\StaticCall ||
            $node instanceof Node\Expr\StaticPropertyFetch ||
            $node instanceof Node\Expr\ClassConstFetch
        ) {
            $nodeToDeduceTypeFrom = $node->class;
        }

        $objectTypes = $this->nodeTypeDeducer->deduce(
            $nodeToDeduceTypeFrom,
            $this->file,
            $this->code,
            $node->getAttribute('startFilePos')
        );

        if (empty($objectTypes)) {
            $this->memberCallList[] = [
                'type'       => self::TYPE_EXPRESSION_HAS_NO_TYPE,
                'memberName' => $node->name->name,
                'start'      => $node->getAttribute('startFilePos') ? $node->getAttribute('startFilePos')   : null,
                'end'        => $node->getAttribute('endFilePos')   ? $node->getAttribute('endFilePos') + 1 : null
            ];

            return;
        }

        foreach ($objectTypes as $objectType) {
            if (!$node->name instanceof Node\Identifier) {
                continue;
            }

            $classInfo = null;

            try {
                $classInfo = $this->classlikeInfoBuilder->getClasslikeInfo($objectType);
            } catch (UnexpectedValueException $e) {
                // Ignore exception, no class information means we return an error anyhow.
            }

            $key = null;

            if ($node instanceof Node\Expr\MethodCall || $node instanceof Node\Expr\StaticCall) {
                $key = 'methods';
            } elseif ($node instanceof Node\Expr\PropertyFetch || $node instanceof Node\Expr\StaticPropertyFetch) {
                $key = 'properties';
            } elseif ($node instanceof Node\Expr\ClassConstFetch) {
                $key = 'constants';
            }

            if (!$classInfo || !isset($classInfo[$key][$node->name->name])) {
                if (!$this->isClassExcluded($objectType)) {
                    if ($previousNode instanceof Node\Expr\Assign ||
                        $previousNode instanceof Node\Expr\AssignOp ||
                        $previousNode instanceof Node\Expr\AssignRef
                    ) {
                        $this->memberCallList[] = [
                            'type'           => self::TYPE_EXPRESSION_NEW_MEMBER_WILL_BE_CREATED,
                            'memberName'     => $node->name->name,
                            'expressionType' => $objectType,
                            'start'          => $node->getAttribute('startFilePos') ? $node->getAttribute('startFilePos')   : null,
                            'end'            => $node->getAttribute('endFilePos')   ? $node->getAttribute('endFilePos') + 1 : null
                        ];
                    } else {
                        $this->memberCallList[] = [
                            'type'           => self::TYPE_EXPRESSION_HAS_NO_SUCH_MEMBER,
                            'memberName'     => $node->name->name,
                            'expressionType' => $objectType,
                            'start'          => $node->getAttribute('startFilePos') ? $node->getAttribute('startFilePos')   : null,
                            'end'            => $node->getAttribute('endFilePos')   ? $node->getAttribute('endFilePos') + 1 : null
                        ];
                    }
                }
            }
        }
    }

    /**
     * @param string $className
     *
     * @return bool
     */
    protected function isClassExcluded(string $className): bool
    {
        $className = $this->typeAnalyzer->getNormalizedFqcn($className);

        return ($className === '\stdClass');
    }

    /**
     * @return array
     */
    public function getMemberCallList(): array
    {
        return $this->memberCallList;
    }
}
