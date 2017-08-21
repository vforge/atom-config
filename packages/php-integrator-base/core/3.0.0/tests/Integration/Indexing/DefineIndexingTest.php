<?php

namespace PhpIntegrator\Tests\Integration\Tooltips;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class DefineIndexingTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testSimpleDefine(): void
    {
        $define = $this->indexDefine('SimpleDefine.phpt');

        $this->assertEquals('DEFINE', $define->getName());
        $this->assertEquals('\DEFINE', $define->getFqcn());
        $this->assertEquals($this->getPathFor('SimpleDefine.phpt'), $define->getFile()->getPath());
        $this->assertEquals(3, $define->getStartLine());
        $this->assertEquals(3, $define->getEndLine());
        $this->assertEquals("'VALUE'", $define->getDefaultValue());
        $this->assertFalse($define->getIsDeprecated());
        $this->assertFalse($define->getHasDocblock());
        $this->assertNull($define->getShortDescription());
        $this->assertNull($define->getLongDescription());
        $this->assertNull($define->getTypeDescription());
        $this->assertCount(1, $define->getTypes());
        $this->assertEquals('string', $define->getTypes()[0]->getType());
        $this->assertEquals('string', $define->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testDefineFqcnWithNamespace(): void
    {
        $constant = $this->indexDefine('DefineFqcnWithNamespace.phpt');

        $this->assertEquals('\N\DEFINE', $constant->getFqcn());
    }

    /**
     * @return void
     */
    public function testChangesArePickedUpOnReindex(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $constants = $this->container->get('managerRegistry')->getRepository(Structures\Constant::class)->findAll();

            $this->assertCount(1, $constants);
            $this->assertEquals('\DEFINE', $constants[0]->getFqcn());

            return str_replace('DEFINE', 'DEFINE2', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $constants = $this->container->get('managerRegistry')->getRepository(Structures\Constant::class)->findAll();

            $this->assertCount(1, $constants);
            $this->assertEquals('\DEFINE2', $constants[0]->getFqcn());
        };

        $path = $this->getPathFor('DefineChanges.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @param string $file
     *
     * @return Structures\Constant
     */
    protected function indexDefine(string $file): Structures\Constant
    {
        $path = $this->getPathFor($file);

        $this->indexTestFile($this->container, $path);

        $constants = $this->container->get('managerRegistry')->getRepository(Structures\Constant::class)->findAll();

        $this->assertCount(1, $constants);

        return $constants[0];
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/DefineIndexingTest/' . $file;
    }
}
