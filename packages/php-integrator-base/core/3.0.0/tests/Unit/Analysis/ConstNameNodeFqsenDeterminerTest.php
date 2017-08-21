<?php

namespace PhpIntegrator\Tests\Unit\Analysis;

use PhpIntegrator\Analysis\Node\ConstNameNodeFqsenDeterminer;

use PhpIntegrator\NameQualificationUtilities\ConstantPresenceIndicatorInterface;

use PhpParser\Node;

class ConstNameNodeFqsenDeterminerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testFullyQualifiedName(): void
    {
        $existenceChecker = $this->getMockBuilder(ConstantPresenceIndicatorInterface::class)
            ->setMethods(['isPresent'])
            ->getMock();

        $existenceChecker->method('isPresent')->will($this->returnValue(false));

        $determiner = new ConstNameNodeFqsenDeterminer($existenceChecker);

        $node = new Node\Name\FullyQualified('\A\B');
        $node->setAttribute('namespace', null);

        $this->assertEquals('\A\B', $determiner->determine($node));
    }

    /**
     * @return void
     */
    public function testQualifiedName(): void
    {
        $existenceChecker = $this->getMockBuilder(ConstantPresenceIndicatorInterface::class)
            ->setMethods(['isPresent'])
            ->getMock();

        $existenceChecker->method('isPresent')->will($this->returnValue(false));

        $determiner = new ConstNameNodeFqsenDeterminer($existenceChecker);

        $namespaceNode = new Node\Name('N');

        $node = new Node\Name('A\B');
        $node->setAttribute('namespace', $namespaceNode);

        $this->assertEquals('\N\A\B', $determiner->determine($node));
    }

    /**
     * @return void
     */
    public function testUnqualifiedNameThatDoesNotExistRelativeToCurrentNamespace(): void
    {
        $existenceChecker = $this->getMockBuilder(ConstantPresenceIndicatorInterface::class)
            ->setMethods(['isPresent'])
            ->getMock();

        $existenceChecker->method('isPresent')->will($this->returnValue(false));

        $determiner = new ConstNameNodeFqsenDeterminer($existenceChecker);

        $namespaceNode = new Node\Name('N');

        $node = new Node\Name('A');
        $node->setAttribute('namespace', $namespaceNode);

        $this->assertEquals('\A', $determiner->determine($node));
    }

    /**
     * @return void
     */
    public function testUnqualifiedNameThatExistsRelativeToCurrentNamespace(): void
    {
        $existenceChecker = $this->getMockBuilder(ConstantPresenceIndicatorInterface::class)
            ->setMethods(['isPresent'])
            ->getMock();

        $existenceChecker->method('isPresent')->will($this->returnValue(true));

        $determiner = new ConstNameNodeFqsenDeterminer($existenceChecker);

        $namespaceNode = new Node\Name('N');

        $node = new Node\Name('A');
        $node->setAttribute('namespace', $namespaceNode);

        $this->assertEquals('\N\A', $determiner->determine($node));
    }
}
