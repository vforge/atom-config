<?php

namespace PhpIntegrator\Tests\Integration\Analysis;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class StructureListProviderTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testRetrievesAllClasses(): void
    {
        $path = __DIR__ . '/StructureListProviderTest/' . 'ClassList.phpt';
        $secondPath = __DIR__ . '/StructureListProviderTest/' . 'FooBarClasses.phpt';

        $this->indexTestFile($this->container, $path);
        $this->indexTestFile($this->container, $secondPath);

        $provider = $this->container->get('structureListProvider');

        $output = $provider->getAll();

        $this->assertEquals(4, count($output));
        $this->assertArrayHasKey('\A\FirstClass', $output);
        $this->assertArrayHasKey('\A\SecondClass', $output);
        $this->assertArrayHasKey('\A\Foo', $output);
        $this->assertArrayHasKey('\A\Bar', $output);
    }
}
