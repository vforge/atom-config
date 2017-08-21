<?php

namespace PhpIntegrator\Indexing;

use DateTime;
use Exception;
use LogicException;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

use PhpIntegrator\Analysis\Typing\Deduction\NodeTypeDeducerInterface;

use PhpIntegrator\NameQualificationUtilities\StructureAwareNameResolverFactoryInterface;

use PhpIntegrator\Parsing\DocblockParser;

use PhpParser\Error;
use PhpParser\Parser;
use PhpParser\ErrorHandler;
use PhpParser\NodeTraverser;

/**
 * Handles indexation of PHP code in a single file.
 *
 * The index only contains "direct" data, meaning that it only contains data that is directly attached to an element.
 * For example, classes will only have their direct members attached in the index. The index will also keep track of
 * links between structural elements and parents, implemented interfaces, and more, but it will not duplicate data,
 * meaning parent methods will not be copied and attached to child classes.
 *
 * The index keeps track of 'outlines' that are confined to a single file. It in itself does not do anything
 * "intelligent" such as automatically inheriting docblocks from overridden methods.
 */
class FileIndexer implements FileIndexerInterface
{
    /**
     * The storage to use for index data.
     *
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var DocblockParser
     */
    private $docblockParser;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @var array
     */
    private $accessModifierMap;

    /**
     * @var array
     */
    private $structureTypeMap;

    /**
     * @var StructureAwareNameResolverFactoryInterface
     */
    private $structureAwareNameResolverFactory;

    /**
     * @param StorageInterface                           $storage
     * @param TypeAnalyzer                               $typeAnalyzer
     * @param DocblockParser                             $docblockParser
     * @param NodeTypeDeducerInterface                   $nodeTypeDeducer
     * @param Parser                                     $parser
     * @param StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory
     */
    public function __construct(
        StorageInterface $storage,
        TypeAnalyzer $typeAnalyzer,
        DocblockParser $docblockParser,
        NodeTypeDeducerInterface $nodeTypeDeducer,
        Parser $parser,
        StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory
    ) {
        $this->storage = $storage;
        $this->typeAnalyzer = $typeAnalyzer;
        $this->docblockParser = $docblockParser;
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        $this->parser = $parser;
        $this->structureAwareNameResolverFactory = $structureAwareNameResolverFactory;
    }

    /**
     * @inheritDoc
     */
    public function index(string $filePath, string $code): void
    {
        $handler = new ErrorHandler\Collecting();

        try {
            $nodes = $this->parser->parse($code, $handler);

            if ($nodes === null) {
                throw new Error('Unknown syntax error encountered');
            }
        } catch (Error $e) {
            throw new IndexingFailedException($e->getMessage(), 0, $e);
        }

        $this->storage->beginTransaction();

        try {
            $file = $this->storage->getFileByPath($filePath);
            $file->setIndexedOn(new DateTime());
        } catch (FileNotFoundStorageException $e) {
            $file = new Structures\File($filePath, new DateTime(), []);
        }

        $this->storage->persist($file);

        try {
            $traverser = $this->runTraverser($nodes, $code, $file);

            $this->storage->commitTransaction();
        } catch (Error $e) {
            $this->storage->rollbackTransaction();

            throw new IndexingFailedException($e->getMessage(), 0, $e);
        } catch (Exception $e) {
            $this->storage->rollbackTransaction();

            throw new LogicException(
                'Could not index file due to an internal exception. This likely means an exception should be caught ' .
                'at a deeper level (if it is acceptable) or there is a bug. The file is "' . $filePath . '" and the ' .
                'exact exception message: "' . $e->getMessage() . '"',
                0,
                $e
            );
        }
    }

    /**
     * @param array           $nodes
     * @param string          $code
     * @param Structures\File $file
     *
     * @return void
     */
    protected function runTraverser(array $nodes, string $code, Structures\File $file): void
    {
        $visitors = $this->getVisitorsForFile($code, $file);

        $useStatementIndexingVisitor = array_shift($visitors);

        // NOTE: Traversing twice may seem absurd, but a rewrite of the use statement indexing visitor to support
        // on-the-fly indexing (i.e. not after the traversal, so it does not need to run separately) seemed to make
        // performance worse, because of the constant flushing and entity changes due to the end lines being
        // recalculated, than just traversing twice.
        $traverser = new NodeTraverser();
        $traverser->addVisitor($useStatementIndexingVisitor);
        $traverser->traverse($nodes);

        $traverser = new NodeTraverser();

        foreach ($visitors as $visitor) {
            $traverser->addVisitor($visitor);
        }

        $traverser->traverse($nodes);
    }

    /**
     * @param string          $code
     * @param Structures\File $file
     *
     * @return array
     */
    protected function getVisitorsForFile(string $code, Structures\File $file): array
    {
        $visitors = [
            new Visiting\UseStatementIndexingVisitor($this->storage, $file, $code),

            new Visiting\ConstantIndexingVisitor(
                $this->storage,
                $this->docblockParser,
                $this->structureAwareNameResolverFactory,
                $this->typeAnalyzer,
                $this->nodeTypeDeducer,
                $file,
                $code
            ),

            new Visiting\DefineIndexingVisitor(
                $this->storage,
                $this->nodeTypeDeducer,
                $file,
                $code
            ),

            new Visiting\FunctionIndexingVisitor(
                $this->structureAwareNameResolverFactory,
                $this->storage,
                $this->docblockParser,
                $this->typeAnalyzer,
                $this->nodeTypeDeducer,
                $file,
                $code
            ),

            new Visiting\ClasslikeIndexingVisitor(
                $this->storage,
                $this->typeAnalyzer,
                $this->docblockParser,
                $this->nodeTypeDeducer,
                $this->structureAwareNameResolverFactory,
                $file,
                $code
            ),

            new Visiting\MetaStaticMethodTypeIndexingVisitor(
                $this->storage,
                $file
            )
        ];

        return $visitors;
    }
}
