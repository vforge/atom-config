<?php

namespace PhpIntegrator\Tests\Integration\Tooltips;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class NamespaceIndexingTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testPermanentStartNamespace(): void
    {
        $path = $this->getPathFor('PermanentStartNamespace.phpt');

        $this->indexTestFile($this->container, $path);

        $file = $this->container->get('storage')->getFileByPath($path);

        $namespaces = $file->getNamespaces();

        $this->assertCount(1, $namespaces);

        $this->assertEquals(0, $namespaces[0]->getStartLine());
        $this->assertEquals(2, $namespaces[0]->getEndLine());
        $this->assertEquals(null, $namespaces[0]->getName());
        $this->assertEquals($path, $namespaces[0]->getFile()->getPath());
        $this->assertEmpty($namespaces[0]->getImports());
    }

    /**
     * @return void
     */
    public function testNormalNamespace(): void
    {
        $path = $this->getPathFor('NormalNamespace.phpt');

        $this->indexTestFile($this->container, $path);

        $file = $this->container->get('storage')->getFileByPath($path);

        $namespaces = $file->getNamespaces();

        $this->assertCount(2, $namespaces);

        $this->assertEquals(3, $namespaces[1]->getStartLine());
        $this->assertEquals(6, $namespaces[1]->getEndLine());
        $this->assertEquals('N', $namespaces[1]->getName());
        $this->assertEquals($path, $namespaces[1]->getFile()->getPath());
        $this->assertEmpty($namespaces[1]->getImports());
    }

    /**
     * @return void
     */
    public function testAnonymousNamespace(): void
    {
        $path = $this->getPathFor('AnonymousNamespace.phpt');

        $this->indexTestFile($this->container, $path);

        $file = $this->container->get('storage')->getFileByPath($path);

        $namespaces = $file->getNamespaces();

        $this->assertCount(2, $namespaces);

        $this->assertEquals(3, $namespaces[1]->getStartLine());
        $this->assertEquals(6, $namespaces[1]->getEndLine());
        $this->assertEquals(null, $namespaces[1]->getName());
        $this->assertEquals($path, $namespaces[1]->getFile()->getPath());
        $this->assertCount(1, $namespaces[1]->getImports());
    }

    /**
     * @return void
     */
    public function testChangesArePickedUpOnReindex(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $file = $container->get('storage')->getFileByPath($path);

            $this->assertCount(3, $file->getNamespaces());
            $this->assertEquals('N', $file->getNamespaces()[1]->getName());

            return str_replace('namespace N', 'namespace ', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $file = $container->get('storage')->getFileByPath($path);

            $this->assertCount(3, $file->getNamespaces());
            $this->assertEquals(null, $file->getNamespaces()[1]->getName());
        };

        $path = $this->getPathFor('NamespaceChanges.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/NamespaceIndexingTest/' . $file;
    }
}
