<?php

namespace PhpIntegrator\Tests\Integration\Analysis;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Contains tests that test whether the registry remains up to date (synchronized) when the state of the index changes.
 */
class ConstantListRegistryIndexSynchronizationTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testNewConstantIsAdded(): void
    {
        $path = $this->getPathFor('NewConstantIsSynchronized.phpt');

        $registry = $this->container->get('constantListProvider.registry');

        $this->assertEmpty($registry->getAll());

        $this->indexTestFile($this->container, $path);

        $this->assertCount(1, $registry->getAll());
        $this->assertArrayHasKey('\TEST', $registry->getAll());
    }

    /**
     * @return void
     */
    public function testOldConstantIsRemoved(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('constantListProvider.registry');

            $this->assertCount(1, $registry->getAll());
            $this->assertArrayHasKey('\TEST', $registry->getAll());

            return str_replace('const TEST', '// const TEST ', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('constantListProvider.registry');

            $this->assertEmpty($registry->getAll());
        };

        $path = $this->getPathFor('OldConstantIsRemoved.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @return void
     */
    public function testExistingConstantIsUpdated(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('constantListProvider.registry');

            $this->assertCount(1, $registry->getAll());
            $this->assertArrayHasKey('\TEST', $registry->getAll());
            $this->assertEquals('1', $registry->getAll()['\TEST']['defaultValue']);

            return str_replace('const TEST = 1', 'const TEST = 2', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $registry = $this->container->get('constantListProvider.registry');

            $this->assertCount(1, $registry->getAll());
            $this->assertArrayHasKey('\TEST', $registry->getAll());
            $this->assertEquals('2', $registry->getAll()['\TEST']['defaultValue']);
        };

        $path = $this->getPathFor('OldConstantIsRemoved.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/ConstantListRegistryIndexSynchronizationTest/' . $file;
    }
}
