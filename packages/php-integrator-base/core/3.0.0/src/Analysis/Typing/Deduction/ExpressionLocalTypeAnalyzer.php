<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Analysis\Visiting\TypeQueryingVisitor;
use PhpIntegrator\Analysis\Visiting\ScopeLimitingVisitor;
use PhpIntegrator\Analysis\Visiting\ExpressionTypeInfoMap;

use PhpIntegrator\Parsing\DocblockParser;

use PhpParser\Parser;
use PhpParser\ErrorHandler;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinterAbstract;

/**
 * Analyzes types affecting expressions (e.g. variables and properties) in a local scope in a file.
 *
 * This class can be used to scan for types that apply to an expression based on local rules, such as conditionals and
 * type overrides.
 */
class ExpressionLocalTypeAnalyzer
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var DocblockParser
     */
    private $docblockParser;

    /**
     * @var PrettyPrinterAbstract
     */
    private $prettyPrinter;

    /**
     * @param Parser                $parser
     * @param DocblockParser        $docblockParser
     * @param PrettyPrinterAbstract $prettyPrinter
     */
    public function __construct(Parser $parser, DocblockParser $docblockParser, PrettyPrinterAbstract $prettyPrinter)
    {
        $this->parser = $parser;
        $this->docblockParser = $docblockParser;
        $this->prettyPrinter = $prettyPrinter;
    }

    /**
     * @param string $code
     * @param int    $offset
     *
     * @return ExpressionTypeInfoMap
     */
    public function analyze(string $code, int $offset): ExpressionTypeInfoMap
    {
        $typeQueryingVisitor = $this->walkTypeQueryingVisitorTo($code, $offset);

        return $typeQueryingVisitor->getExpressionTypeInfoMap();
    }

    /**
     * @param string $code
     * @param int    $offset
     *
     * @throws UnexpectedValueException
     *
     * @return TypeQueryingVisitor
     */
    protected function walkTypeQueryingVisitorTo(string $code, int $offset): TypeQueryingVisitor
    {
        $nodes = null;

        $handler = new ErrorHandler\Collecting();

        try {
            $nodes = $this->parser->parse($code, $handler);
        } catch (\PhpParser\Error $e) {
            throw new UnexpectedValueException('Parsing the file failed!');
        }

        // In php-parser 2.x, this happens when you enter $this-> before an if-statement, because of a syntax error that
        // it can not recover from.
        if ($nodes === null) {
            throw new UnexpectedValueException('Parsing the file failed!');
        }

        $scopeLimitingVisitor = new ScopeLimitingVisitor($offset);
        $typeQueryingVisitor = new TypeQueryingVisitor($this->docblockParser, $this->prettyPrinter, $offset);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($scopeLimitingVisitor);
        $traverser->addVisitor($typeQueryingVisitor);
        $traverser->traverse($nodes);

        return $typeQueryingVisitor;
    }
}
