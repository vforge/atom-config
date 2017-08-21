<?php

namespace PhpIntegrator\Tests\Integration\Analysis;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Contains tests that test whether the registry remains up to date (synchronized) when the state of the index changes.
 */
class StructureListRegistryIndexSynchronizationTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testNewStructureIsAdded(): void
    {
        $path = $this->getPathFor('NewStructureIsSynchronized.phpt');

        $registry = $this->container->get('structureListProvider.registry');

        $this->assertEmpty($registry->getAll());

        $this->indexTestFile($this->container, $path);

        $this->assertCount(1, $registry->getAll());
        $this->assertArrayHasKey('\Test', $registry->getAll());
    }

    /**
     * @return void
     */
    public function testOldStructureIsRemoved(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('structureListProvider.registry');

            $this->assertCount(1, $registry->getAll());
            $this->assertArrayHasKey('\Test', $registry->getAll());

            return str_replace('class Test', '// class Test ', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('structureListProvider.registry');

            $this->assertEmpty($registry->getAll());
        };

        $path = $this->getPathFor('OldStructureIsRemoved.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @return void
     */
    public function testExistingStructureIsUpdated(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('structureListProvider.registry');

            $this->assertCount(1, $registry->getAll());
            $this->assertArrayHasKey('\Test', $registry->getAll());
            $this->assertFalse($registry->getAll()['\Test']['isFinal']);

            return str_replace('class Test {}', 'final class Test {}', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('structureListProvider.registry');

            $this->assertCount(1, $registry->getAll());
            $this->assertArrayHasKey('\Test', $registry->getAll());
            $this->assertTrue($registry->getAll()['\Test']['isFinal']);
        };

        $path = $this->getPathFor('OldStructureIsRemoved.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/StructureListRegistryIndexSynchronizationTest/' . $file;
    }
}
