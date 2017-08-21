<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Analysis\ClasslikeInfoBuilder;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;
use PhpIntegrator\Analysis\Typing\FileStructureListProviderInterface;

use PhpIntegrator\Common\Position;
use PhpIntegrator\Common\FilePosition;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\NameQualificationUtilities\StructureAwareNameResolverFactoryInterface;

use PhpIntegrator\Utility\NodeHelpers;
use PhpIntegrator\Utility\SourceCodeHelpers;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Name} node.
 */
class NameNodeTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var ClasslikeInfoBuilder
     */
    private $classlikeInfoBuilder;

    /**
     * @var FileStructureListProviderInterface
     */
    private $fileStructureListProvider;

    /**
     * @var StructureAwareNameResolverFactoryInterface
     */
    private $structureAwareNameResolverFactory;

    /**
     * @param TypeAnalyzer                               $typeAnalyzer
     * @param ClasslikeInfoBuilder                       $classlikeInfoBuilder
     * @param FileStructureListProviderInterface         $fileStructureListProvider
     * @param StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory
     */
    public function __construct(
        TypeAnalyzer $typeAnalyzer,
        ClasslikeInfoBuilder $classlikeInfoBuilder,
        FileStructureListProviderInterface $fileStructureListProvider,
        StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory
    ) {
        $this->typeAnalyzer = $typeAnalyzer;
        $this->classlikeInfoBuilder = $classlikeInfoBuilder;
        $this->fileStructureListProvider = $fileStructureListProvider;
        $this->structureAwareNameResolverFactory = $structureAwareNameResolverFactory;
    }

    /**
     * @inheritDoc
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Name) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromNameNode($node, $file, $code, $offset);
    }

    /**
     * @param Node\Name       $node
     * @param Structures\File $file
     * @param string          $code
     * @param int             $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromNameNode(Node\Name $node, Structures\File $file, string $code, int $offset): array
    {
        $nameString = NodeHelpers::fetchClassName($node);

        if ($nameString === 'static' || $nameString === 'self') {
            $currentClass = $this->findCurrentClassAt($file, $code, $offset);

            if ($currentClass === null) {
                return [];
            }

            return [$this->typeAnalyzer->getNormalizedFqcn($currentClass)];
        } elseif ($nameString === 'parent') {
            $currentClassName = $this->findCurrentClassAt($file, $code, $offset);

            if (!$currentClassName) {
                return [];
            }

            $classInfo = $this->classlikeInfoBuilder->getClasslikeInfo($currentClassName);

            if (!$classInfo || empty($classInfo['parents'])) {
                return [];
            }

            $type = $classInfo['parents'][0];

            return [$this->typeAnalyzer->getNormalizedFqcn($type)];
        }

        $line = SourceCodeHelpers::calculateLineByOffset($code, $offset);

        $filePosition = new FilePosition(
            $file->getPath(),
            new Position($line, 0)
        );

        $fqcn = $this->structureAwareNameResolverFactory->create($filePosition)->resolve($nameString, $filePosition);

        return [$fqcn];
    }

    /**
     * @param Structures\File $file
     * @param string          $source
     * @param int             $offset
     *
     * @return string|null
     */
    protected function findCurrentClassAt(Structures\File $file, string $source, int $offset): ?string
    {
        $line = SourceCodeHelpers::calculateLineByOffset($source, $offset);

        return $this->findCurrentClassAtLine($file, $source, $line);
    }

    /**
     * @param Structures\File $file
     * @param string          $source
     * @param int             $line
     *
     * @return string|null
     */
    protected function findCurrentClassAtLine(Structures\File $file, string $source, int $line): ?string
    {
        $classes = $this->fileStructureListProvider->getAllForFile($file);

        foreach ($classes as $fqcn => $class) {
            if ($line >= $class['startLine'] && $line <= $class['endLine']) {
                return $fqcn;
            }
        }

        return null;
    }
}
