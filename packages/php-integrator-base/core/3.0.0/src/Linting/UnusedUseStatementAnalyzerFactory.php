<?php

namespace PhpIntegrator\Linting;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

use PhpIntegrator\Parsing\DocblockParser;

/**
 * Factory that produces instances of {@see UnusedUseStatementAnalyzer}.
 */
class UnusedUseStatementAnalyzerFactory
{
    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var DocblockParser
     */
    private $docblockParser;

    /**
     * @param TypeAnalyzer   $typeAnalyzer
     * @param DocblockParser $docblockParser
     */
    public function __construct(TypeAnalyzer $typeAnalyzer, DocblockParser $docblockParser)
    {
        $this->typeAnalyzer = $typeAnalyzer;
        $this->docblockParser = $docblockParser;
    }

    /**
     * @param string $code
     *
     * @return UnusedUseStatementAnalyzer
     */
    public function create(string $code): UnusedUseStatementAnalyzer
    {
        return new UnusedUseStatementAnalyzer(
            $this->typeAnalyzer,
            $this->docblockParser,
            $code
        );
    }
}
