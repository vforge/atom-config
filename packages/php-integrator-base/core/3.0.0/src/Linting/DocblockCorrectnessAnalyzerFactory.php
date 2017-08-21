<?php

namespace PhpIntegrator\Linting;

use PhpIntegrator\Analysis\DocblockAnalyzer;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;
use PhpIntegrator\Analysis\Typing\ParameterDocblockTypeSemanticEqualityChecker;

use PhpIntegrator\Parsing\DocblockParser;

/**
 * Factory that produces instances of {@see DocblockCorrectnessAnalyzer}.
 */
class DocblockCorrectnessAnalyzerFactory
{
    /**
     * @var ParameterDocblockTypeSemanticEqualityChecker
     */
    private $parameterDocblockTypeSemanticEqualityChecker;

    /**
     * @var DocblockParser
     */
    private $docblockParser;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var DocblockAnalyzer
     */
    private $docblockAnalyzer;

    /**
     * @param ParameterDocblockTypeSemanticEqualityChecker $parameterDocblockTypeSemanticEqualityChecker
     * @param DocblockParser                               $docblockParser
     * @param TypeAnalyzer                                 $typeAnalyzer
     * @param DocblockAnalyzer                             $docblockAnalyzer
     */
    public function __construct(
        ParameterDocblockTypeSemanticEqualityChecker $parameterDocblockTypeSemanticEqualityChecker,
        DocblockParser $docblockParser,
        TypeAnalyzer $typeAnalyzer,
        DocblockAnalyzer $docblockAnalyzer
    ) {
        $this->parameterDocblockTypeSemanticEqualityChecker = $parameterDocblockTypeSemanticEqualityChecker;
        $this->docblockParser = $docblockParser;
        $this->typeAnalyzer = $typeAnalyzer;
        $this->docblockAnalyzer = $docblockAnalyzer;
    }

    /**
     * @param string $file
     * @param string $code
     *
     * @return DocblockCorrectnessAnalyzer
     */
    public function create(string $file, string $code): DocblockCorrectnessAnalyzer
    {
        return new DocblockCorrectnessAnalyzer(
            $file,
            $code,
            $this->parameterDocblockTypeSemanticEqualityChecker,
            $this->docblockParser,
            $this->typeAnalyzer,
            $this->docblockAnalyzer
        );
    }
}
