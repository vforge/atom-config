<?php

namespace PhpIntegrator\Parsing;

use LogicException;

use PhpIntegrator\Parsing\Node\Expr;

use PhpParser\Node;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ErrorHandler;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\NodeVisitorAbstract;

/**
 * Parses partial (incomplete) PHP code.
 *
 * This class can parse PHP code that is incomplete (and thus erroneous), which is only partially supported by
 * php-parser. This is necessary for being able to deal with incomplete expressions such as "$this->" to see what the
 * type of the expression is. This information can in turn be used by client functionality such as autocompletion.
 */
class PartialParser implements Parser
{
    /**
     * @var Parser
     */
    private $strictParser;

    /**
     * @var ParserFactory
     */
    private $parserFactory;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @param ParserFactory $parserFactory
     * @param Lexer         $lexer
     */
    public function __construct(ParserFactory $parserFactory, Lexer $lexer)
    {
        $this->parserFactory = $parserFactory;
        $this->lexer = $lexer;
    }

    /**
     * @inheritDoc
     */
    public function parse(string $code, ErrorHandler $errorHandler = null)
    {
        if ($errorHandler) {
            throw new LogicException('Error handling is not supported as error recovery will be attempted automatically');
        }

        $correctedExpression = $this->getNormalizedCode($code);

        $nodes = $this->tryParse($correctedExpression);
        $nodes = $nodes ?: $this->tryParseWithKeywordCorrection($correctedExpression);
        $nodes = $nodes ?: $this->tryParseWithTrailingSemicolonCorrection($correctedExpression);
        $nodes = $nodes ?: $this->tryParseWithHeredocTerminationCorrection($correctedExpression);
        $nodes = $nodes ?: $this->tryParseWithFunctionTerminationCorrection($correctedExpression);
        $nodes = $nodes ?: $this->tryParseWithFunctionMissingArgumentCorrection($correctedExpression);
        $nodes = $nodes ?: $this->tryParseWithTernaryOperatorTerminationCorrection($correctedExpression);
        $nodes = $nodes ?: $this->tryParseWithDummyInsertion($correctedExpression);

        return $nodes;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    protected function getNormalizedCode(string $code): string
    {
        if (mb_substr(trim($code), 0, 5) !== '<?php') {
            return '<?php ' . $code;
        };

        return $code;
    }

    /**
     * @param string $code
     *
     * @return Node[]|null
     */
    protected function tryParseWithKeywordCorrection(string $code): ?array
    {
        if (mb_strrpos($code, 'self') === (mb_strlen($code) - mb_strlen('self'))) {
            return [new \PhpIntegrator\Parsing\Node\Keyword\Self_()];
        } elseif (mb_strrpos($code, 'static') === (mb_strlen($code) - mb_strlen('static'))) {
            return [new \PhpIntegrator\Parsing\Node\Keyword\Static_()];
        } elseif (mb_strrpos($code, 'parent') === (mb_strlen($code) - mb_strlen('parent'))) {
            return [new \PhpIntegrator\Parsing\Node\Keyword\Parent_()];
        }

        return null;
    }

    /**
     * @param string $code
     *
     * @return Node[]|null
     */
    protected function tryParseWithTrailingSemicolonCorrection(string $code): ?array
    {
        return $this->tryParse($code . ';');
    }

    /**
     * @param string $code
     *
     * @return Node[]|null
     */
    protected function tryParseWithHeredocTerminationCorrection(string $code): ?array
    {
        return $this->tryParse($code . ";\n"); // Heredocs need to be suffixed by a semicolon and a newline.
    }

    /**
     * @param string $code
     *
     * @return array|null
     */
    protected function tryParseWithFunctionTerminationCorrection(string $code): ?array
    {
        return $this->tryParse($code . ");");
    }

    /**
     * @param string $code
     *
     * @return array|null
     */
    protected function tryParseWithFunctionMissingArgumentCorrection(string $code): ?array
    {
        $dummyName = '____DUMMY____';

        $nodes = $this->tryParse($code . " {$dummyName});");

        if (empty($nodes)) {
            return $nodes;
        }

        $node = $nodes[count($nodes) - 1];

        if ($node instanceof Node\Stmt\Expression) {
            if ($node->expr instanceof Node\Expr\MethodCall ||
                $node->expr instanceof Node\Expr\FuncCall ||
                $node->expr instanceof Node\Expr\StaticCall ||
                $node->expr instanceof Node\Expr\New_
            ) {
                foreach ($node->expr->args as $i => $arg) {
                    if ($arg->value instanceof Node\Expr\ConstFetch && $arg->value->name->toString() === $dummyName) {
                        array_splice($node->expr->args, $i, $i+1);
                        break;
                    }
                }
            }
        }

        return $nodes;
    }

    /**
     * @param string $code
     *
     * @return Node[]|null
     */
    protected function tryParseWithTernaryOperatorTerminationCorrection(string $code): ?array
    {
        $dummyName = '____DUMMY____';

        $nodes = $this->tryParse($code . ": {$dummyName};");

        if (empty($nodes)) {
            return $nodes;
        }

        $node = $nodes[count($nodes) - 1];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class($dummyName) extends NodeVisitorAbstract {
            private $dummyName;

            public function __construct(string $dummyName)
            {
                $this->dummyName = $dummyName;
            }

            public function enterNode(Node $node)
            {
                if ($node instanceof Node\Expr\Ternary) {
                    if ($node->else instanceof Node\Expr\ConstFetch && $node->else->name->toString() === $this->dummyName) {
                        $node->else = new Expr\Dummy();
                    }
                }
            }
        });

        $traverser->traverse($nodes);

        return $nodes;
    }

    /**
     * @param string $code
     *
     * @return Node[]|null
     */
    protected function tryParseWithDummyInsertion(string $code): ?array
    {
        $removeDummy = false;
        $dummyName = '____DUMMY____';

        $nodes = $this->tryParse($code . $dummyName . ';');

        if (empty($nodes)) {
            return null;
        }

        $node = $nodes[count($nodes) - 1];

        if ($node instanceof Node\Stmt\Expression) {
            if ($node->expr instanceof Node\Expr\ClassConstFetch || $node->expr instanceof Node\Expr\PropertyFetch) {
                if ($node->expr->name->name === $dummyName) {
                    $node->expr->name->name = '';
                }
            }
        }


        return $nodes;
    }

    /**
     * @param string $code
     *
     * @return Node[]|null
     */
    protected function tryParse(string $code): ?array
    {
        try {
            return $this->getStrictParser()->parse($code);
        } catch (\PhpParser\Error $e) {
            return null;
        }

        return null;
    }

    /**
     * @return Parser
     */
    protected function getStrictParser(): Parser
    {
        if (!$this->strictParser instanceof Parser) {
            $this->strictParser = $this->parserFactory->create(ParserFactory::PREFER_PHP7, $this->lexer);
        }

        return $this->strictParser;
    }
}
