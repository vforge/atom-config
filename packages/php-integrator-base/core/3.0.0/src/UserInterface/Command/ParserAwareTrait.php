<?php

namespace PhpIntegrator\UserInterface\Command;

use UnexpectedValueException;

use PhpParser\Parser;
use PhpParser\ErrorHandler;

/**
 * Trait making a class aware of a parser.
 */
trait ParserAwareTrait
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @param string            $code
     * @param ErrorHandler|null $errorHandler
     *
     * @throws UnexpectedValueException
     *
     * @return \PhpParser\Node[]
     */
    protected function parse(string $code, ?ErrorHandler $errorHandler = null): array
    {
        try {
            $nodes = $this->parser->parse($code, $errorHandler);
        } catch (\PhpParser\Error $e) {
            throw new UnexpectedValueException('Parsing the file failed!');
        }

        if ($nodes === null) {
            throw new UnexpectedValueException('Parsing the file failed!');
        }

        return $nodes;
    }
}
