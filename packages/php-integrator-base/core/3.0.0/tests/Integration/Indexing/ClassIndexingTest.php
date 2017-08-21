<?php

namespace PhpIntegrator\Tests\Integration\Tooltips;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClassIndexingTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testSimpleClass(): void
    {
        $structure = $this->indexClass('SimpleClass.phpt');

        $this->assertEquals('Test', $structure->getName());
        $this->assertEquals('\Test', $structure->getFqcn());
        $this->assertEquals($this->getPathFor('SimpleClass.phpt'), $structure->getFile()->getPath());
        $this->assertEquals(3, $structure->getStartLine());
        $this->assertEquals(6, $structure->getEndLine());
        $this->assertNull($structure->getShortDescription());
        $this->assertNull($structure->getLongDescription());
        $this->assertFalse($structure->getIsDeprecated());
        $this->assertFalse($structure->getHasDocblock());
        $this->assertCount(1, $structure->getConstants());
        $this->assertEmpty($structure->getProperties());
        $this->assertEmpty($structure->getMethods());
        $this->assertFalse($structure->getIsAbstract());
        $this->assertFalse($structure->getIsFinal());
        $this->assertFalse($structure->getIsAnnotation());
        $this->assertNull($structure->getParentFqcn());
        $this->assertEmpty($structure->getChildFqcns());
        $this->assertEmpty($structure->getInterfaceFqcns());
        $this->assertEmpty($structure->getTraitFqcns());
        $this->assertEmpty($structure->getTraitAliases());
        $this->assertEmpty($structure->getTraitPrecedences());
    }

    /**
     * @return void
     */
    public function testClassNamespace(): void
    {
        $structure = $this->indexClass('ClassNamespace.phpt');

        $this->assertEquals('\N\Test', $structure->getFqcn());
    }

    /**
     * @return void
     */
    public function testClassShortDescription(): void
    {
        $structure = $this->indexClass('ClassShortDescription.phpt');

        $this->assertEquals('A summary.', $structure->getShortDescription());
    }

    /**
     * @return void
     */
    public function testClassLongDescription(): void
    {
        $structure = $this->indexClass('ClassLongDescription.phpt');

        $this->assertEquals('A long description.', $structure->getLongDescription());
    }

    /**
     * @return void
     */
    public function testDeprecatedClass(): void
    {
        $structure = $this->indexClass('DeprecatedClass.phpt');

        $this->assertTrue($structure->getIsDeprecated());
    }

    /**
     * @return void
     */
    public function testClassWithDocblock(): void
    {
        $structure = $this->indexClass('ClassWithDocblock.phpt');

        $this->assertTrue($structure->getHasDocblock());
    }

    /**
     * @return void
     */
    public function testAbstractClass(): void
    {
        $structure = $this->indexClass('AbstractClass.phpt');

        $this->assertTrue($structure->getIsAbstract());
    }

    /**
     * @return void
     */
    public function testFinalClass(): void
    {
        $structure = $this->indexClass('FinalClass.phpt');

        $this->assertTrue($structure->getIsFinal());
    }

    /**
     * @return void
     */
    public function testAnnotationClass(): void
    {
        $structure = $this->indexClass('AnnotationClass.phpt');

        $this->assertTrue($structure->getIsAnnotation());
    }

    /**
     * @return void
     */
    public function testClassParentChildRelationship(): void
    {
        $path = $this->getPathFor('ClassParentChildRelationship.phpt');

        $this->indexTestFile($this->container, $path);

        $entities = $this->container->get('managerRegistry')->getRepository(Structures\Class_::class)->findAll();

        $this->assertCount(2, $entities);

        $this->assertCount(1, $entities[0]->getChildFqcns());
        $this->assertEquals($entities[1]->getFqcn(), $entities[0]->getChildFqcns()[0]);
        $this->assertEquals($entities[0]->getFqcn(), $entities[1]->getParentFqcn());
    }

    /**
     * @return void
     */
    public function testClassInterface(): void
    {
        $structure = $this->indexClass('ClassInterface.phpt');

        $this->assertCount(1, $structure->getInterfaceFqcns());
        $this->assertEquals('\I', $structure->getInterfaceFqcns()[0]);
    }

    /**
     * @return void
     */
    public function testClassTrait(): void
    {
        $structure = $this->indexClass('ClassTrait.phpt');

        $this->assertCount(2, $structure->getTraitFqcns());
        $this->assertEquals('\A', $structure->getTraitFqcns()[0]);
        $this->assertEquals('\B', $structure->getTraitFqcns()[1]);
    }

    /**
     * @return void
     */
    public function testClassTraitAlias(): void
    {
        $structure = $this->indexClass('ClassTraitAlias.phpt');

        $this->assertCount(1, $structure->getTraitAliases());
        $this->assertEquals($structure, $structure->getTraitAliases()[0]->getClass());
        $this->assertNull($structure->getTraitAliases()[0]->getTraitFqcn());
        $this->assertNull($structure->getTraitAliases()[0]->getAccessModifier());
        $this->assertEquals('foo', $structure->getTraitAliases()[0]->getName());
        $this->assertEquals('bar', $structure->getTraitAliases()[0]->getAlias());
    }

    /**
     * @return void
     */
    public function testClassTraitAliasWithTraitName(): void
    {
        $structure = $this->indexClass('ClassTraitAliasWithTraitName.phpt');

        $this->assertCount(1, $structure->getTraitAliases());
        $this->assertEquals('\A', $structure->getTraitAliases()[0]->getTraitFqcn());
    }

    /**
     * @return void
     */
    public function testClassTraitAliasWithAccessModifier(): void
    {
        $structure = $this->indexClass('ClassTraitAliasWithAccessModifier.phpt');

        $this->assertCount(1, $structure->getTraitAliases());
        $this->assertNotNull($structure->getTraitAliases()[0]->getAccessModifier());
        $this->assertEquals('protected', $structure->getTraitAliases()[0]->getAccessModifier()->getName());
    }

    /**
     * @return void
     */
    public function testClassTraitPrecedence(): void
    {
        $structure = $this->indexClass('ClassTraitPrecedence.phpt');

        $this->assertCount(1, $structure->getTraitPrecedences());
        $this->assertEquals($structure, $structure->getTraitPrecedences()[0]->getClass());
        $this->assertEquals('\A', $structure->getTraitPrecedences()[0]->getTraitFqcn());
        $this->assertEquals('foo', $structure->getTraitPrecedences()[0]->getName());
    }

    /**
     * @return void
     */
    public function testRenameChangeIsPickedUpOnReindex(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $structures = $this->container->get('managerRegistry')->getRepository(Structures\Class_::class)->findAll();

            $this->assertCount(1, $structures);

            $structure = $structures[0];

            $this->assertEquals('Test', $structure->getName());

            return str_replace('Test', 'Test2 ', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $structures = $this->container->get('managerRegistry')->getRepository(Structures\Class_::class)->findAll();

            $this->assertCount(1, $structures);

            $structure = $structures[0];

            $this->assertEquals('Test2', $structure->getName());
        };

        $path = $this->getPathFor('ClassRenameChange.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @return void
     */
    public function testParentChangeIsPickedUpOnReindex(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $structures = $this->container->get('managerRegistry')->getRepository(Structures\Class_::class)->findAll();

            $this->assertCount(3, $structures);

            $structure = $structures[2];

            $this->assertEquals('\Parent1', $structure->getParentFqcn());

            return str_replace('Parent1', 'Parent2 ', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $structures = $this->container->get('managerRegistry')->getRepository(Structures\Class_::class)->findAll();

            $this->assertCount(3, $structures);

            $structure = $structures[2];

            $this->assertEquals('\Parent2', $structure->getParentFqcn());
        };

        $path = $this->getPathFor('ClassParentChange.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @param string $file
     *
     * @return Structures\Class_
     */
    protected function indexClass(string $file): Structures\Class_
    {
        $path = $this->getPathFor($file);

        $this->indexTestFile($this->container, $path);

        $entities = $this->container->get('managerRegistry')->getRepository(Structures\Class_::class)->findAll();

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
        return __DIR__ . '/ClassIndexingTest/' . $file;
    }
}
