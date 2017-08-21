<?php

namespace PhpIntegrator\Tests\Integration\Tooltips;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class FilePruningTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testPruneRemovedFiles(): void
    {
        $path = __DIR__ . '/FilePruningTest';

        $testFilePath = $path . '/file.php';

        file_put_contents($testFilePath, '<?php class A {}');

        $this->assertTrue(file_exists($testFilePath), 'Could not create test file');

        $this->indexPath($this->container, $path);

        $files = $this->container->get('storage')->getFiles();

        $this->assertCount(1, $files);
        $this->assertEquals($testFilePath, $files[0]->getPath());

        unlink($testFilePath);

        $this->container->get('projectIndexer')->pruneRemovedFiles();

        $files = $this->container->get('storage')->getFiles();

        $this->assertEmpty($files);
    }
}
