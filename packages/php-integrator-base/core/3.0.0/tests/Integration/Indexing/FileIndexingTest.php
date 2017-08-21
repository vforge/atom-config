<?php

namespace PhpIntegrator\Tests\Integration\Tooltips;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class FileIndexingTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testFileTimestampIsUpdatedOnReindex(): void
    {
        $path = $this->getPathFor('TestFile.php');

        $code = '<?php class A {}';

        $this->container->get('fileIndexer')->index($path, $code);

        $files = $this->container->get('storage')->getFiles();

        $this->assertCount(1, $files);

        $timestamp = $files[0]->getIndexedOn();

        $this->container->get('fileIndexer')->index($path, $code);

        $files = $this->container->get('storage')->getFiles();

        $this->assertCount(1, $files);
        $this->assertTrue($files[0]->getIndexedOn() > $timestamp);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/FileIndexingTest/' . $file;
    }
}
