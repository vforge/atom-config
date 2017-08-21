<?php

namespace PhpIntegrator\Tests\Integration\UserInterface\Command;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class GlobalConstantsCommandTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testGlobalConstants(): void
    {
        $output = $this->getGlobalConstants('GlobalConstants.phpt');

        $this->assertThat($output, $this->arrayHasKey('\DEFINE_CONSTANT'));
        $this->assertEquals($output['\DEFINE_CONSTANT']['name'], 'DEFINE_CONSTANT');
        $this->assertEquals($output['\DEFINE_CONSTANT']['fqcn'], '\DEFINE_CONSTANT');

        $this->assertThat($output, $this->arrayHasKey('\A\DEFINE_CONSTANT_NAMESPACED'));
        $this->assertEquals($output['\A\DEFINE_CONSTANT_NAMESPACED']['name'], 'DEFINE_CONSTANT_NAMESPACED');
        $this->assertEquals($output['\A\DEFINE_CONSTANT_NAMESPACED']['fqcn'], '\A\DEFINE_CONSTANT_NAMESPACED');

        $this->assertThat($output, $this->arrayHasKey('\A\FIRST_CONSTANT'));
        $this->assertEquals($output['\A\FIRST_CONSTANT']['name'], 'FIRST_CONSTANT');
        $this->assertEquals($output['\A\FIRST_CONSTANT']['fqcn'], '\A\FIRST_CONSTANT');

        $this->assertThat($output, $this->arrayHasKey('\A\SECOND_CONSTANT'));
        $this->assertEquals($output['\A\SECOND_CONSTANT']['name'], 'SECOND_CONSTANT');
        $this->assertEquals($output['\A\SECOND_CONSTANT']['fqcn'], '\A\SECOND_CONSTANT');

        $this->assertThat($output, $this->logicalNot($this->arrayHasKey('SHOULD_NOT_SHOW_UP')));
    }

    /**
     * @return void
     */
    public function testCorrectlyFetchesDefaultValueOfDefineWithExpression(): void
    {
        $output = $this->getGlobalConstants('DefineWithExpression.phpt');

        $this->assertEquals('(($version{0} * 10000) + ($version{2} * 100) + $version{4})', $output['\TEST_CONSTANT']['defaultValue']);
    }

    /**
     * @return void
     */
    public function testCorrectlyFetchesDefaultValueOfDefineWithIncompleteConstFetch(): void
    {
        $output = $this->getGlobalConstants('DefineWithIncompleteConstFetch.phpt');

        $this->assertEquals('\Test::', $output['\TEST_CONSTANT']['defaultValue']);
    }

    /**
     * @param string $file
     *
     * @return array
     */
    protected function getGlobalConstants(string $file): array
    {
        $path = $this->getPathFor($file);

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('globalConstantsCommand');

        return $command->getGlobalConstants();
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/GlobalConstantsCommandTest/' . $file;
    }
}
