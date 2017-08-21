<?php

namespace PhpIntegrator\Tests\Integration\Analysis\Typing;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class FileStructureListProviderTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testShowsOnlyClassesForRequestedFile(): void
    {
        $path = __DIR__ . '/FileStructureListProviderTest/' . 'ClassList.phpt';
        $secondPath = __DIR__ . '/FileStructureListProviderTest/' . 'FooBarClasses.phpt';

        $this->indexTestFile($this->container, $path);
        $this->indexTestFile($this->container, $secondPath);

        $provider = $this->container->get('fileStructureListProvider');

        $file = $this->container->get('storage')->getFileByPath($path);

        $output = $provider->getAllForFile($file);

        $this->assertEquals(2, count($output));
        $this->assertArrayHasKey('\A\FirstClass', $output);
        $this->assertArrayHasKey('\A\SecondClass', $output);
        $this->assertArrayNotHasKey('\A\Foo', $output);
        $this->assertArrayNotHasKey('\A\Bar', $output);
    }
}
