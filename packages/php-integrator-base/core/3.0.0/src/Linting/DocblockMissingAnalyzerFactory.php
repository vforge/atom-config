<?php

namespace PhpIntegrator\Linting;

use PhpIntegrator\Analysis\ClasslikeInfoBuilder;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

/**
 * Factory that produces instances of {@see DocblockMissingAnalyzer}.
 */
class DocblockMissingAnalyzerFactory
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
     * @param TypeAnalyzer         $typeAnalyzer
     * @param ClasslikeInfoBuilder $classlikeInfoBuilder
     */
    public function __construct(TypeAnalyzer $typeAnalyzer, ClasslikeInfoBuilder $classlikeInfoBuilder)
    {
        $this->typeAnalyzer = $typeAnalyzer;
        $this->classlikeInfoBuilder = $classlikeInfoBuilder;
    }

    /**
     * @param string $code
     *
     * @return DocblockMissingAnalyzer
     */
    public function create(string $code): DocblockMissingAnalyzer
    {
        return new DocblockMissingAnalyzer(
            $code,
            $this->typeAnalyzer,
            $this->classlikeInfoBuilder
        );
    }
}
