<?php

namespace PhpIntegrator\Tests\Integration\Tooltips;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use PhpParser\Node;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class MetaStaticMethodTypesIndexingTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testStaticMethodTypes(): void
    {
        $path = $this->getPathFor('StaticMethodTypes.phpt');

        $this->indexTestFile($this->container, $path);

        $types = $this->container->get('metadataProvider')->getMetaStaticMethodTypesFor('\A\Foo', 'get');

        $this->assertCount(2, $types);

        $this->assertEquals(0, $types[0]->getArgumentIndex());
        $this->assertEquals('bar', $types[0]->getValue());
        $this->assertEquals(Node\Scalar\String_::class, $types[0]->getValueNodeType());
        $this->assertEquals('\B\Bar', $types[0]->getReturnType());

        $this->assertEquals(0, $types[1]->getArgumentIndex());
        $this->assertEquals('car', $types[1]->getValue());
        $this->assertEquals(Node\Scalar\String_::class, $types[1]->getValueNodeType());
        $this->assertEquals('\B\Car', $types[1]->getReturnType());
    }

    /**
     * @return void
     */
    public function testChangesArePickedUpOnReindex(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $types = $this->container->get('metadataProvider')->getMetaStaticMethodTypesFor('\A\Foo', 'get');

            $this->assertCount(1, $types);

            $this->assertEquals(0, $types[0]->getArgumentIndex());
            $this->assertEquals('bar', $types[0]->getValue());
            $this->assertEquals(Node\Scalar\String_::class, $types[0]->getValueNodeType());
            $this->assertEquals('\A\Bar', $types[0]->getReturnType());

            return str_replace('\A\\', '\B\\', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $types = $this->container->get('metadataProvider')->getMetaStaticMethodTypesFor('\A\Foo', 'get');
            $this->assertCount(0, $types);

            $types = $this->container->get('metadataProvider')->getMetaStaticMethodTypesFor('\B\Foo', 'get');
            $this->assertCount(1, $types);

            $this->assertEquals(0, $types[0]->getArgumentIndex());
            $this->assertEquals('bar', $types[0]->getValue());
            $this->assertEquals(Node\Scalar\String_::class, $types[0]->getValueNodeType());
            $this->assertEquals('\B\Bar', $types[0]->getReturnType());
        };

        $path = $this->getPathFor('StaticMethodTypeChanges.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/MetaStaticMethodTypesIndexingTest/' . $file;
    }
}
