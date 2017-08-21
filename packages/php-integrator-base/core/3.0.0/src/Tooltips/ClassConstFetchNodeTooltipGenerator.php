<?php

namespace PhpIntegrator\Tooltips;

use UnexpectedValueException;

use PhpIntegrator\Analysis\ClasslikeInfoBuilder;

use PhpIntegrator\Analysis\Typing\Deduction\NodeTypeDeducerInterface;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Provides tooltips for {@see Node\Expr\ClassConstFetch} nodes.
 */
class ClassConstFetchNodeTooltipGenerator
{
    /**
     * @var ConstantTooltipGenerator
     */
    private $constantTooltipGenerator;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @var ClasslikeInfoBuilder
     */
    private $classlikeInfoBuilder;

    /**
     * @param ConstantTooltipGenerator $constantTooltipGenerator
     * @param NodeTypeDeducerInterface $nodeTypeDeducer
     * @param ClasslikeInfoBuilder     $classlikeInfoBuilder
     */
    public function __construct(
        ConstantTooltipGenerator $constantTooltipGenerator,
        NodeTypeDeducerInterface $nodeTypeDeducer,
        ClasslikeInfoBuilder $classlikeInfoBuilder
    ) {
        $this->constantTooltipGenerator = $constantTooltipGenerator;
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        $this->classlikeInfoBuilder = $classlikeInfoBuilder;
    }

    /**
     * @param Node\Expr\ClassConstFetch $node
     * @param Structures\File           $file
     * @param string                    $code
     *
     * @throws UnexpectedValueException when the constant name is not a string (i.e. an error node).
     * @throws UnexpectedValueException when the type of the class could not be determined.
     * @throws UnexpectedValueException when no tooltips could be determined.
     *
     * @return string
     */
    public function generate(Node\Expr\ClassConstFetch $node, Structures\File $file, string $code): string
    {
        if (!$node->name instanceof Node\Identifier) {
            throw new UnexpectedValueException("Can't deduce the type of a non-string node");
        }

        $classTypes = $this->getClassTypes($node, $file, $code);

        $tooltips = [];

        foreach ($classTypes as $classType) {
            $constantInfo = $this->fetchClassConstantInfo($classType, $node->name);

            if ($constantInfo === null) {
                continue;
            }

            $tooltips[] = $this->constantTooltipGenerator->generate($constantInfo);
        }

        if (empty($tooltips)) {
            throw new UnexpectedValueException('Could not determine any tooltips for the class constant');
        }

        // Fetch the first tooltip. In theory, multiple tooltips are possible, but we don't support these at the moment.
        return $tooltips[0];
    }

    /**
     * @param Node\Expr\ClassConstFetch $node
     * @param Structures\File           $file
     * @param string                    $code
     *
     * @throws UnexpectedValueException
     *
     * @return array
     */
    protected function getClassTypes(Node\Expr\ClassConstFetch $node, Structures\File $file, string $code): array
    {
        $classTypes = [];

        try {
            $classTypes = $this->nodeTypeDeducer->deduce($node->class, $file, $code, $node->getAttribute('startFilePos'));
        } catch (UnexpectedValueException $e) {
            throw new UnexpectedValueException('Could not deduce the type of class', 0, $e);
        }

        if (empty($classTypes)) {
            throw new UnexpectedValueException('No types returned for class');
        }

        return $classTypes;
    }

    /**
     * @param string $classType
     * @param string $name
     *
     * @return array
     */
    protected function fetchClassConstantInfo(string $classType, string $name): ?array
    {
        $classInfo = null;

        try {
            $classInfo = $this->classlikeInfoBuilder->getClasslikeInfo($classType);
        } catch (UnexpectedValueException $e) {
            return null;
        }

        if (!isset($classInfo['constants'][$name])) {
            return null;
        }

        return $classInfo['constants'][$name];
    }
}
