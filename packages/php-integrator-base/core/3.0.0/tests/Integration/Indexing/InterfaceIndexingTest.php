<?php

namespace PhpIntegrator\Tests\Integration\Tooltips;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class InterfaceIndexingTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testSimpleInterface(): void
    {
        $structure = $this->indexInterface('SimpleInterface.phpt');

        $this->assertEquals('Test', $structure->getName());
        $this->assertEquals('\Test', $structure->getFqcn());
        $this->assertEquals($this->getPathFor('SimpleInterface.phpt'), $structure->getFile()->getPath());
        $this->assertEquals(3, $structure->getStartLine());
        $this->assertEquals(6, $structure->getEndLine());
        $this->assertNull($structure->getShortDescription());
        $this->assertNull($structure->getLongDescription());
        $this->assertFalse($structure->getIsDeprecated());
        $this->assertFalse($structure->getHasDocblock());
        $this->assertCount(1, $structure->getConstants());
        $this->assertEmpty($structure->getProperties());
        $this->assertEmpty($structure->getMethods());
        $this->assertEmpty($structure->getParentFqcns());
        $this->assertEmpty($structure->getChildFqcns());
        $this->assertEmpty($structure->getImplementorFqcns());
    }

    /**
     * @return void
     */
    public function testInterfaceNamespace(): void
    {
        $structure = $this->indexInterface('InterfaceNamespace.phpt');

        $this->assertEquals('\N\Test', $structure->getFqcn());
    }

    /**
     * @return void
     */
    public function testInterfaceShortDescription(): void
    {
        $structure = $this->indexInterface('InterfaceShortDescription.phpt');

        $this->assertEquals('A summary.', $structure->getShortDescription());
    }

    /**
     * @return void
     */
    public function testInterfaceLongDescription(): void
    {
        $structure = $this->indexInterface('InterfaceLongDescription.phpt');

        $this->assertEquals('A long description.', $structure->getLongDescription());
    }

    /**
     * @return void
     */
    public function testDeprecatedInterface(): void
    {
        $structure = $this->indexInterface('DeprecatedInterface.phpt');

        $this->assertTrue($structure->getIsDeprecated());
    }

    /**
     * @return void
     */
    public function testInterfaceWithDocblock(): void
    {
        $structure = $this->indexInterface('InterfaceWithDocblock.phpt');

        $this->assertTrue($structure->getHasDocblock());
    }

    /**
     * @return void
     */
    public function testInterfaceParentChildRelationship(): void
    {
        $path = $this->getPathFor('InterfaceParentChildRelationship.phpt');

        $this->indexTestFile($this->container, $path);

        $entities = $this->container->get('managerRegistry')->getRepository(Structures\Interface_::class)->findAll();

        $this->assertCount(3, $entities);

        $this->assertEmpty($entities[0]->getParentFqcns());
        $this->assertCount(2, $entities[0]->getChildFqcns());
        $this->assertEquals($entities[1]->getFqcn(), $entities[0]->getChildFqcns()[0]);
        $this->assertEquals($entities[2]->getFqcn(), $entities[0]->getChildFqcns()[1]);

        $this->assertCount(1, $entities[1]->getParentFqcns());
        $this->assertEquals($entities[0]->getFqcn(), $entities[1]->getParentFqcns()[0]);
        $this->assertCount(1, $entities[1]->getChildFqcns());
        $this->assertEquals($entities[2]->getFqcn(), $entities[1]->getChildFqcns()[0]);

        $this->assertCount(2, $entities[2]->getParentFqcns());
        $this->assertEquals($entities[0]->getFqcn(), $entities[2]->getParentFqcns()[0]);
        $this->assertEquals($entities[1]->getFqcn(), $entities[2]->getParentFqcns()[1]);
        $this->assertEmpty($entities[2]->getChildFqcns());
    }

    /**
     * @return void
     */
    public function testInterfaceImplementor(): void
    {
        $structure = $this->indexInterface('InterfaceImplementor.phpt');

        $this->assertCount(1, $structure->getImplementorFqcns());
        $this->assertEquals('\C', $structure->getImplementorFqcns()[0]);
    }

    /**
     * @return void
     */
    public function testChangesArePickedUpOnReindex(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $structures = $this->container->get('managerRegistry')->getRepository(Structures\Interface_::class)->findAll();

            $this->assertCount(1, $structures);

            $structure = $structures[0];

            $this->assertEquals('Test', $structure->getName());

            return str_replace('Test', 'Test2 ', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $structures = $this->container->get('managerRegistry')->getRepository(Structures\Interface_::class)->findAll();

            $this->assertCount(1, $structures);

            $structure = $structures[0];

            $this->assertEquals('Test2', $structure->getName());
        };

        $path = $this->getPathFor('InterfaceChanges.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @param string $file
     *
     * @return Structures\Interface_
     */
    protected function indexInterface(string $file): Structures\Interface_
    {
        $path = $this->getPathFor($file);

        $this->indexTestFile($this->container, $path);

        $entities = $this->container->get('managerRegistry')->getRepository(Structures\Interface_::class)->findAll();

        $this->assertCount(1, $entities);

        return $entities[0];
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/InterfaceIndexingTest/' . $file;
    }
}
