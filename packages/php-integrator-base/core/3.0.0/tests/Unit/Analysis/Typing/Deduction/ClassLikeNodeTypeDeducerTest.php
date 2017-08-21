<?php

namespace PhpIntegrator\Tests\Unit\Analysis\Typing\Deduction;

use DateTime;

use PhpIntegrator\Analysis\Typing\Deduction\ClassLikeNodeTypeDeducer;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

class ClassLikeNodeTypeDeducerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ClassLikeNodeTypeDeducer
     */
    private $classLikeNodeTypeDeducer;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->classLikeNodeTypeDeducer = new ClassLikeNodeTypeDeducer();
    }

    /**
     * @return void
     */
    public function testClassNode(): void
    {
        $node = new Node\Stmt\Class_('A');

        $file = new Structures\File('', new DateTime(), []);

        $this->assertEquals(['A'], $this->classLikeNodeTypeDeducer->deduce($node, $file, '', 0));
    }

    /**
     * @return void
     */
    public function testInterfaceNode(): void
    {
        $node = new Node\Stmt\Interface_('A');

        $file = new Structures\File('', new DateTime(), []);

        $this->assertEquals(['A'], $this->classLikeNodeTypeDeducer->deduce($node, $file, '', 0));
    }

    /**
     * @return void
     */
    public function testTraitNode(): void
    {
        $node = new Node\Stmt\Trait_('A');

        $file = new Structures\File('', new DateTime(), []);

        $this->assertEquals(['A'], $this->classLikeNodeTypeDeducer->deduce($node, $file, '', 0));
    }

    /**
     * @return void
     */
    public function testAnonymousClassNode(): void
    {
        $node = new Node\Stmt\Class_(null);

        $file = new Structures\File('', new DateTime(), []);

        $this->assertEquals([], $this->classLikeNodeTypeDeducer->deduce($node, $file, '', 0));
    }
}
