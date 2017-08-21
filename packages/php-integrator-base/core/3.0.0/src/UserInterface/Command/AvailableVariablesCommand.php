<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;

use PhpIntegrator\Analysis\VariableScanner;

use PhpIntegrator\Utility\SourceCodeHelpers;
use PhpIntegrator\Utility\SourceCodeStreamReader;

use PhpParser\Parser;
use PhpParser\ErrorHandler;

/**
 * Command that shows information about the scopes at a specific position in a file.
 */
class AvailableVariablesCommand extends AbstractCommand
{
    use ParserAwareTrait;

    /**
     * @var VariableScanner
     */
    private $variableScanner;

    /**
     * @var SourceCodeStreamReader
     */
    private $sourceCodeStreamReader;

    /**
     * @param VariableScanner        $variableScanner
     * @param Parser                 $parser
     * @param SourceCodeStreamReader $sourceCodeStreamReader
     */
    public function __construct(
        VariableScanner $variableScanner,
        Parser $parser,
        SourceCodeStreamReader $sourceCodeStreamReader
    ) {
        $this->variableScanner = $variableScanner;
        $this->parser = $parser;
        $this->sourceCodeStreamReader = $sourceCodeStreamReader;
    }

    /**
     * @inheritDoc
     */
    public function execute(ArrayAccess $arguments)
    {
        if (!isset($arguments['offset'])) {
            throw new InvalidArgumentsException('An --offset must be supplied into the source code!');
        }

        $code = null;

        if (isset($arguments['stdin']) && $arguments['stdin']) {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromStdin();
        } elseif (isset($arguments['file']) && $arguments['file']) {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromFile($arguments['file']);
        } else {
            throw new InvalidArgumentsException('Either a --file file must be supplied or --stdin must be passed!');
        }

        $offset = $arguments['offset'];

        if (isset($arguments['charoffset']) && $arguments['charoffset'] == true) {
            $offset = SourceCodeHelpers::getByteOffsetFromCharacterOffset($offset, $code);
        }

        return $this->getAvailableVariables($code, $offset);
     }

    /**
     * @param string $code
     * @param int    $offset
     *
     * @return array
     */
     public function getAvailableVariables(string $code, int $offset): array
     {
         $handler = new ErrorHandler\Collecting();

         $nodes = $this->parse($code, $handler);

         return $this->variableScanner->getAvailableVariables($nodes, $offset);
     }
}
