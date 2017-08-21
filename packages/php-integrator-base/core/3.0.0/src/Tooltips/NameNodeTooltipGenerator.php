<?php

namespace PhpIntegrator\Tooltips;

use UnexpectedValueException;

use PhpIntegrator\Analysis\ClasslikeInfoBuilder;

use PhpIntegrator\Analysis\Node\NameNodeFqsenDeterminer;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Provides tooltips for {@see Node\Name} nodes.
 */
class NameNodeTooltipGenerator
{
    /**
     * @var ClassLikeTooltipGenerator
     */
    private $classLikeTooltipGenerator;

    /**
     * @var NameNodeFqsenDeterminer
     */
    private $nameNodeFqsenDeterminer;

    /**
     * @var ClasslikeInfoBuilder
     */
    private $classLikeInfoBuilder;

    /**
     * @param ClassLikeTooltipGenerator $classLikeTooltipGenerator
     * @param NameNodeFqsenDeterminer   $nameNodeFqsenDeterminer
     * @param ClasslikeInfoBuilder      $classLikeInfoBuilder
     */
    public function __construct(
        ClassLikeTooltipGenerator $classLikeTooltipGenerator,
        NameNodeFqsenDeterminer $nameNodeFqsenDeterminer,
        ClasslikeInfoBuilder $classLikeInfoBuilder
    ) {
        $this->classLikeTooltipGenerator = $classLikeTooltipGenerator;
        $this->nameNodeFqsenDeterminer = $nameNodeFqsenDeterminer;
        $this->classLikeInfoBuilder = $classLikeInfoBuilder;
    }

    /**
     * @param Node\Name       $node
     * @param Structures\File $file
     * @param int             $line
     *
     * @throws UnexpectedValueException when the constant was not found.
     *
     * @return string
     */
    public function generate(Node\Name $node, Structures\File $file, int $line): string
    {
        $fqsen = $this->nameNodeFqsenDeterminer->determine($node, $file, $line);

        $info = $this->getClassLikeInfo($fqsen);

        return $this->classLikeTooltipGenerator->generate($info);
    }

    /**
     * @param string $fullyQualifiedName
     *
     * @throws UnexpectedValueException
     *
     * @return array
     */
    protected function getClassLikeInfo(string $fullyQualifiedName): array
    {
        return $this->classLikeInfoBuilder->getClasslikeInfo($fullyQualifiedName);
    }
}
