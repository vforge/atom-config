<?php

namespace PhpIntegrator\Tests\Unit\Analysis\Visiting;

use PhpIntegrator\Analysis\Visiting\ScopeLimitingVisitor;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

class ScopeLimitingVisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testThatTheVisitingOperationIsNonDestructiveAndNodesAreNotPermanentlyModifiedButOnlyDuringTraversal(): void
    {
        $code = <<<'SOURCE'
            <?php

            namespace A;

            class Foo
            {
                protected $prop;

                public function someMethod()
                {
                    if ($a instanceof \Traversable) {

                    } elseif ($b instanceof \DateTime) {
                        $b->format();
                    } elseif ($c instanceof self) {
                        $d = $c->prop;
                    }
                }
            }
SOURCE;

        $code = trim($code);

        $lexer = new Lexer([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'
            ]
        ]);

        $parserFactory = new ParserFactory();

        $parser = $parserFactory->create(ParserFactory::PREFER_PHP7, $lexer, []);

        $nodes = $parser->parse($code);

        $stateBefore = serialize($nodes);

        $visitor = new ScopeLimitingVisitor(260);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);

        $stateAfter = serialize($nodes);

        $this->assertTrue(
            $stateBefore === $stateAfter,
            'Using a ScopeLimitingVisitor is destructive. If it alters the state of the nodes, it must also restore them on exit.'
        );
    }
}
