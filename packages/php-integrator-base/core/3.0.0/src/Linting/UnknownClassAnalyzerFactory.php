<?php

namespace PhpIntegrator\Linting;

use PhpIntegrator\Analysis\ClasslikeExistenceCheckerInterface;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

use PhpIntegrator\NameQualificationUtilities\StructureAwareNameResolverFactoryInterface;

use PhpIntegrator\Parsing\DocblockParser;

/**
 * Factory that produces instances of {@see UnknownClassAnalyzer}.
 */
class UnknownClassAnalyzerFactory
{
    /**
     * @var ClasslikeExistenceCheckerInterface
     */
    private $classlikeExistenceChecker;

    /**
     * @var StructureAwareNameResolverFactoryInterface
     */
    private $structureAwareNameResolverFactory;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var DocblockParser
     */
    private $docblockParser;

    /**
     * @param ClasslikeExistenceCheckerInterface         $classlikeExistenceChecker
     * @param StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory
     * @param TypeAnalyzer                               $typeAnalyzer
     * @param DocblockParser                             $docblockParser
     */
    public function __construct(
        ClasslikeExistenceCheckerInterface $classlikeExistenceChecker,
        StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory,
        TypeAnalyzer $typeAnalyzer,
        DocblockParser $docblockParser
    ) {
        $this->classlikeExistenceChecker = $classlikeExistenceChecker;
        $this->structureAwareNameResolverFactory = $structureAwareNameResolverFactory;
        $this->typeAnalyzer = $typeAnalyzer;
        $this->docblockParser = $docblockParser;
    }

    /**
     * @param string $file
     *
     * @return UnknownClassAnalyzer
     */
    public function create(string $file): UnknownClassAnalyzer
    {
        return new UnknownClassAnalyzer(
            $this->classlikeExistenceChecker,
            $this->structureAwareNameResolverFactory,
            $this->typeAnalyzer,
            $this->docblockParser,
            $file
        );
    }
}
