<?php

namespace PhpIntegrator\Tests\Integration\Analysis;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Contains tests that test whether the registry remains up to date (synchronized) when the state of the index changes.
 */
class NamespaceListRegistryIndexSynchronizationTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testNewNamespaceIsAdded(): void
    {
        $path = $this->getPathFor('NewNamespaceIsSynchronized.phpt');

        $registry = $this->container->get('namespaceListProvider.registry');

        $this->assertEmpty($registry->getAll());

        $this->indexTestFile($this->container, $path);

        $this->assertCount(2, $registry->getAll());
        $this->assertEquals('Test', $registry->getAll()[1]['name']);
    }

    /**
     * @return void
     */
    public function testOldNamespaceIsRemoved(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('namespaceListProvider.registry');

            $this->assertCount(2, $registry->getAll());
            $this->assertEquals('Test', $registry->getAll()[1]['name']);

            return str_replace('namespace Test', '// namespace Test ', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('namespaceListProvider.registry');

            $this->assertCount(1, $registry->getAll());
        };

        $path = $this->getPathFor('OldNamespaceIsRemoved.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @return void
     */
    public function testExistingNamespaceIsUpdated(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('namespaceListProvider.registry');

            $this->assertCount(2, $registry->getAll());
            $this->assertEquals('Test', $registry->getAll()[1]['name']);
            $this->assertEquals(4, $registry->getAll()[1]['endLine']);

            return str_replace('namespace Test;', "namespace Test;\n\n", $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('namespaceListProvider.registry');

            $this->assertCount(2, $registry->getAll());
            $this->assertEquals('Test', $registry->getAll()[1]['name']);
            $this->assertEquals(6, $registry->getAll()[1]['endLine']);
        };

        $path = $this->getPathFor('OldNamespaceIsRemoved.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/NamespaceListRegistryIndexSynchronizationTest/' . $file;
    }
}
