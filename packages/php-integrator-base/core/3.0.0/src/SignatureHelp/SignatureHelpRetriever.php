<?php

namespace PhpIntegrator\SignatureHelp;

use LogicException;
use UnexpectedValueException;

use PhpIntegrator\Analysis\Node\FunctionFunctionInfoRetriever;
use PhpIntegrator\Analysis\Node\MethodCallMethodInfoRetriever;

use PhpIntegrator\Analysis\Visiting\NodeFetchingVisitor;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\Parsing\ParserTokenHelper;

use PhpIntegrator\PrettyPrinting\FunctionParameterPrettyPrinter;

use PhpIntegrator\Utility\NodeHelpers;

use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\ErrorHandler;
use PhpParser\NodeTraverser;

/**
 * Retrieves invocation information for function and method calls.
 */
class SignatureHelpRetriever
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var ParserTokenHelper
     */
    private $parserTokenHelper;

    /**
     * @var FunctionFunctionInfoRetriever
     */
    private $functionFunctionInfoRetriever;

    /**
     * @var MethodCallMethodInfoRetriever
     */
    private $methodCallMethodInfoRetriever;

    /**
     * @var FunctionParameterPrettyPrinter
     */
    private $functionParameterPrettyPrinter;

    /**
     * @param Parser                         $parser
     * @param ParserTokenHelper              $parserTokenHelper
     * @param FunctionFunctionInfoRetriever  $functionFunctionInfoRetriever
     * @param MethodCallMethodInfoRetriever  $methodCallMethodInfoRetriever
     * @param FunctionParameterPrettyPrinter $functionParameterPrettyPrinter
     */
    public function __construct(
        Parser $parser,
        ParserTokenHelper $parserTokenHelper,
        FunctionFunctionInfoRetriever $functionFunctionInfoRetriever,
        MethodCallMethodInfoRetriever $methodCallMethodInfoRetriever,
        FunctionParameterPrettyPrinter $functionParameterPrettyPrinter
    ) {
        $this->parser = $parser;
        $this->parserTokenHelper = $parserTokenHelper;
        $this->functionFunctionInfoRetriever = $functionFunctionInfoRetriever;
        $this->methodCallMethodInfoRetriever = $methodCallMethodInfoRetriever;
        $this->functionParameterPrettyPrinter = $functionParameterPrettyPrinter;
    }

    /**
     * @param Structures\File $file
     * @param string          $code
     * @param int             $position
     *
     * @throws UnexpectedValueException when there is no signature help to be retrieved for the location.
     * @throws UnexpectedValueException when a node type is encountered that this method doesn't know how to handle.
     *
     * @return SignatureHelp
     */
    public function get(
        Structures\File $file,
        string $code,
        int $position
    ): SignatureHelp {
        $nodes = [];

        // try {
            $nodes = $this->getNodesFromCode($code);
            $node = $this->getNodeAt($nodes, $position);

            return $this->getSignatureHelpForNode($node, $file, $code, $position);
        // } catch (UnexpectedValueException $e) {
        //     return null;
        // }
    }

    /**
     * @param array $nodes
     * @param int   $position
     *
     * @throws UnexpectedValueException
     *
     * @return Node
     */
    protected function getNodeAt(array $nodes, int $position): Node
    {
        $visitor = new NodeFetchingVisitor($position);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        $node = $visitor->getNode();

        if (!$node) {
            throw new UnexpectedValueException('No node found at location ' . $position);
        }

        return $node;
    }

    /**
     * @param Node            $node
     * @param Structures\File $file
     * @param string          $code
     * @param int             $position
     *
     * @throws UnexpectedValueException
     *
     * @return SignatureHelp
     */
    protected function getSignatureHelpForNode(Node $node, Structures\File $file, string $code, int $position): SignatureHelp
    {
        $invocationNode = NodeHelpers::findNodeOfAnyTypeInNodePath(
            $node,
            Node\Expr\FuncCall::class,
            Node\Expr\StaticCall::class,
            Node\Expr\MethodCall::class,
            Node\Expr\New_::class
        );

        if (!$invocationNode) {
            throw new UnexpectedValueException('No node supporting signature help found at location ' . $position);
        }

        $argumentNode = NodeHelpers::findNodeOfAnyTypeInNodePath($node, Node\Arg::class);

        if ($argumentNode && $argumentNode->getAttribute('parent') !== $invocationNode) {
            // Usually the invocationNode will be the parent, but in case we're on the name of a nested function call,
            // we may have received the wrong node instead. We also can't fetch the argument beforehand, as we may be
            // at a location in between arguments where $argumentNode will be null (as its range only spans the actual
            // argument, without whitespace and optional comma).
            $nodeNameEndFilePosition = null;

            if ($invocationNode instanceof Node\Expr\FuncCall) {
                $nodeNameEndFilePosition = $invocationNode->name->getAttribute('endFilePos') + 1;
            } elseif ($invocationNode instanceof Node\Expr\New_) {
                $nodeNameEndFilePosition = $invocationNode->class->getAttribute('endFilePos') + 1;
            } elseif ($invocationNode instanceof Node\Expr\MethodCall || $invocationNode instanceof Node\Expr\StaticCall) {
                $nodeNameEndFilePosition = $invocationNode->name->getAttribute('endFilePos') + 1;
            } else {
                throw new LogicException(
                    'Unexpected invocation node type "' . get_class($invocationNode) . '" encountered'
                );
            }

            if ($position <= $nodeNameEndFilePosition) {
                $invocationNode = $argumentNode->getAttribute('parent');
            }
        }

        $argumentIndex = $this->getArgumentIndex($invocationNode, $code, $position);

        return $this->generateResponseFor($invocationNode, $argumentIndex, $file, $code, $position);
    }

    /**
     * @param Node\Expr\FuncCall|Node\Expr\StaticCall|Node\Expr\MethodCall|Node\Expr\New_ $invocationNode
     * @param string                                                                      $code
     * @param int                                                                         $position
     *
     * @return int
     */
    protected function getArgumentIndex(Node $invocationNode, string $code, int $position): int
    {
        $arguments = $invocationNode->args;

        if (empty($arguments)) {
            for ($i = $position; $i < $invocationNode->getAttribute('endFilePos'); ++$i) {
                if ($code[$i] === '(') {
                    throw new UnexpectedValueException(
                        'Found node supporting signature help at location ' . $position . ', but it\'s outside the ' .
                        'range of the argument list'
                    );
                }
            }

            return 0;
        }

        $startOfArgumentList = null;

        for ($i = $arguments[0]->getAttribute('startFilePos') - 1; $i >= 0; --$i) {
            if ($code[$i] === '(') {
                $startOfArgumentList = $i;
                break;
            }
        }

        $endOfArgumentList = null;

        for ($i = $arguments[count($arguments) - 1]->getAttribute('endFilePos') + 1; $i < mb_strlen($code); ++$i) {
            if ($code[$i] === ')') {
                $endOfArgumentList = $i+1;
                break;
            }
        }

        if ($position <= $startOfArgumentList || $position > $endOfArgumentList) {
            throw new UnexpectedValueException(
                'Found node supporting signature help at location ' . $position . ', but it\'s outside the ' .
                'range of the argument list'
            );
        }

        $argumentNodeAfter = null;
        $argumentNodeBefore = null;

        foreach ($arguments as $argument) {
            // NOTE: Node end positions are inclusive rather than exclusive.
            if ($position >= ($argument->getAttribute('endFilePos')+1)) {
                $argumentNodeBefore = $argument;
            }

            if (!$argumentNodeAfter && $position <= $argument->getAttribute('startFilePos')) {
                $argumentNodeAfter = $argument;
            }
        }

        if (!$argumentNodeBefore) {
            return 0;
        }

        $isBeforeComma = true;

        for ($i = $argumentNodeBefore->getAttribute('endFilePos') + 1; $i < $position; ++$i) {
            if ($code[$i] === ',') {
                $isBeforeComma = false;
                break;
            }
        }

        $argumentIndex = array_search($argumentNodeBefore, $arguments, true);

        // By offsetting from the argument before, we catch the case where there is a syntax error, which causes no
        // last node to exist.
        return $argumentIndex + ($isBeforeComma ? 0 : 1);
    }

    /**
     * @param Node            $node
     * @param int             $argumentIndex
     * @param Structures\File $file
     * @param string          $code
     * @param int             $offset
     *
     * @throws UnexpectedValueException
     *
     * @return SignatureHelp
     */
    protected function generateResponseFor(
        Node $node,
        int $argumentIndex,
        Structures\File $file,
        string $code,
        int $offset
    ): SignatureHelp {
        $name = null;
        $parameters = [];
        $documentation = null;

        if (
            $node instanceof Node\Expr\MethodCall ||
            $node instanceof Node\Expr\StaticCall ||
            $node instanceof Node\Expr\New_
        ) {
            $methodInfoElements = $this->methodCallMethodInfoRetriever->retrieve($node, $file, $code, $offset);

            if (empty($methodInfoElements)) {
                throw new UnexpectedValueException('Method to fetch signature help for was not found');
            }

            // FIXME: There could be multiple matches, return multiple signatures in that case.
            $methodInfo = array_shift($methodInfoElements);

            return $this->generateResponseFromFunctionInfo($methodInfo, $argumentIndex);
        } elseif ($node instanceof Node\Expr\FuncCall) {
            $functionInfo = $this->functionFunctionInfoRetriever->retrieve($node);

            return $this->generateResponseFromFunctionInfo($functionInfo, $argumentIndex);
        }

        throw new UnexpectedValueException(
            'Could not determine signature help for node of type ' . get_class($node)
        );
    }

    /**
     * @param array $functionInfo
     * @param int   $argumentIndex
     *
     * @throws UnexpectedValueException
     *
     * @return SignatureHelp
     */
    protected function generateResponseFromFunctionInfo(array $functionInfo, int $argumentIndex): SignatureHelp
    {
        $name = $functionInfo['name'];
        $documentation = $functionInfo['shortDescription'];
        $parameters = $this->getResponseParametersForFunctionParameters($functionInfo['parameters']);

        $argumentIndex = $this->getNormalizedFunctionArgumentIndex($functionInfo, $argumentIndex);

        $signature = new SignatureInformation($name, $documentation, $parameters);

        return new SignatureHelp([$signature], 0, $argumentIndex);
    }

    /**
     * @param array $functionInfo
     * @param int   $argumentIndex
     *
     * @throws UnexpectedValueException
     *
     * @return int|null
     */
    protected function getNormalizedFunctionArgumentIndex(array $functionInfo, int $argumentIndex): ?int
    {
        $parameterCount = count($functionInfo['parameters']);

        if ($argumentIndex >= $parameterCount) {
            if ($argumentIndex === 0 && $parameterCount === 0) {
                // First "argument" here isn't actually an argument, so we return null as index so we can still show
                // signature help for functions without arguments.
                return null;
            } elseif ($parameterCount > 0 && $functionInfo['parameters'][$parameterCount - 1]['isVariadic']) {
                return $parameterCount - 1;
            } else {
                throw new UnexpectedValueException(
                    "Argument index {$argumentIndex} is out of bounds, only {$parameterCount} parameters supported"
                );
            }
        }

        return $argumentIndex;
    }

    /**
     * @param array $parameters
     *
     * @return ParameterInformation[]
     */
    protected function getResponseParametersForFunctionParameters(array $parameters): array
    {
        $responseParameters = [];

        foreach ($parameters as $parameter) {
            $responseParameters[] = $this->getResponseParametersForFunctionParameter($parameter);
        }

        return $responseParameters;
    }

    /**
     * @param array $parameter
     *
     * @return ParameterInformation
     */
    protected function getResponseParametersForFunctionParameter(array $parameter): ParameterInformation
    {
        $label = $this->functionParameterPrettyPrinter->print($parameter);

        return new ParameterInformation($label, $parameter['description']);
    }

    /**
     * @param string $code
     *
     * @throws UnexpectedValueException
     *
     * @return Node[]
     */
    protected function getNodesFromCode(string $code): array
    {
        $nodes = $this->parser->parse($code, $this->getErrorHandler());

        if ($nodes === null) {
            throw new UnexpectedValueException('No nodes returned after parsing code');
        }

        return $nodes;
    }

    /**
     * @return ErrorHandler\Collecting
     */
    protected function getErrorHandler(): ErrorHandler\Collecting
    {
        return new ErrorHandler\Collecting();
    }
}
