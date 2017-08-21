<?php

namespace PhpIntegrator\Tests\Integration\Tooltips;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests file indexing in combination with the file namespace provider.
 *
 * The file namespace provider performs caching, so these integration tests ensure that the cache is properly cleared
 * when the source changes.
 */
class FileIndexerFileNamespaceProviderCombinationTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testNewImportsArePickedUpIn(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $results = $container->get('fileNamespaceProvider')->provide($path);

            $this->assertCount(3, $results);
            $this->assertEmpty($results[2]->getImports());

            return str_replace('// ', '', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $results = $container->get('fileNamespaceProvider')->provide($path);

            $this->assertCount(3, $results);
            $this->assertCount(1, $results[2]->getImports(), 'Failed asserting that file namespace provider picks up new imports after reindex');
        };

        $path = $this->getPathFor('NewImportClearsCache.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/FileIndexerFileNamespaceProviderCombinationTest/' . $file;
    }
}
