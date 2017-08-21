<?php

namespace PhpIntegrator\Tests\Integration\Analysis;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Contains tests that test whether the registry remains up to date (synchronized) when the state of the index changes.
 */
class FunctionListRegistryIndexSynchronizationTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testNewFunctionIsAdded(): void
    {
        $path = $this->getPathFor('NewFunctionIsSynchronized.phpt');

        $registry = $this->container->get('functionListProvider.registry');

        $this->assertEmpty($registry->getAll());

        $this->indexTestFile($this->container, $path);

        $this->assertCount(1, $registry->getAll());
        $this->assertArrayHasKey('\test', $registry->getAll());
    }

    /**
     * @return void
     */
    public function testOldFunctionIsRemoved(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('functionListProvider.registry');

            $this->assertCount(1, $registry->getAll());
            $this->assertArrayHasKey('\test', $registry->getAll());

            return str_replace('function test', '// function test ', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('functionListProvider.registry');

            $this->assertEmpty($registry->getAll());
        };

        $path = $this->getPathFor('OldFunctionIsRemoved.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @return void
     */
    public function testExistingFunctionIsUpdated(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('functionListProvider.registry');

            $this->assertCount(1, $registry->getAll());
            $this->assertArrayHasKey('\test', $registry->getAll());
            $this->assertEmpty($registry->getAll()['\test']['parameters']);

            return str_replace('function test()', 'function test(int $a) ', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('functionListProvider.registry');

            $this->assertCount(1, $registry->getAll());
            $this->assertArrayHasKey('\test', $registry->getAll());
            $this->assertCount(1, $registry->getAll()['\test']['parameters']);
        };

        $path = $this->getPathFor('OldFunctionIsRemoved.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/FunctionListRegistryIndexSynchronizationTest/' . $file;
    }
}
