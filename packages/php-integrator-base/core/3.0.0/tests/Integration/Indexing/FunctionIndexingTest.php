<?php

namespace PhpIntegrator\Tests\Integration\Tooltips;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class FunctionIndexingTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testSimpleFunction(): void
    {
        $function = $this->indexFunction('SimpleFunction.phpt');

        $this->assertEquals('foo', $function->getName());
        $this->assertEquals('\foo', $function->getFqcn());
        $this->assertEquals($this->getPathFor('SimpleFunction.phpt'), $function->getFile()->getPath());
        $this->assertEquals(3, $function->getStartLine());
        $this->assertEquals(6, $function->getEndLine());
        $this->assertFalse($function->getIsDeprecated());
        $this->assertNull($function->getShortDescription());
        $this->assertNull($function->getLongDescription());
        $this->assertNull($function->getReturnDescription());
        $this->assertNull($function->getReturnTypeHint());
        $this->assertFalse($function->getHasDocblock());
        $this->assertEmpty($function->getThrows());
        $this->assertEmpty($function->getParameters());
        $this->assertEmpty($function->getReturnTypes());
    }

    /**
     * @return void
     */
    public function testDeprecatedFunction(): void
    {
        $function = $this->indexFunction('DeprecatedFunction.phpt');

        $this->assertTrue($function->getIsDeprecated());
    }

    /**
     * @return void
     */
    public function testFunctionShortDescription(): void
    {
        $function = $this->indexFunction('FunctionShortDescription.phpt');

        $this->assertEquals('This is a summary.', $function->getShortDescription());
    }

    /**
     * @return void
     */
    public function testFunctionLongDescription(): void
    {
        $function = $this->indexFunction('FunctionLongDescription.phpt');

        $this->assertEquals('This is a long description.', $function->getLongDescription());
    }

    /**
     * @return void
     */
    public function testFunctionReturnDescription(): void
    {
        $function = $this->indexFunction('FunctionReturnDescription.phpt');

        $this->assertEquals('This is a return description.', $function->getReturnDescription());
    }

    /**
     * @return void
     */
    public function testFunctionReturnTypeIsFetchedFromDocblockAndGetsPrecedenceOverReturnTypeHint(): void
    {
        $function = $this->indexFunction('FunctionReturnTypeFromDocblock.phpt');

        $this->assertEquals('int', $function->getReturnTypes()[0]->getType());
        $this->assertEquals('int', $function->getReturnTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testFunctionReturnTypeIsFetchedFromReturnTypeHint(): void
    {
        $function = $this->indexFunction('FunctionReturnTypeFromTypeHint.phpt');

        $this->assertEquals('string', $function->getReturnTypes()[0]->getType());
        $this->assertEquals('string', $function->getReturnTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testFunctionReturnTypeHint(): void
    {
        $function = $this->indexFunction('FunctionReturnTypeHint.phpt');

        $this->assertEquals('string', $function->getReturnTypeHint());
    }

    /**
     * @return void
     */
    public function testFunctionExplicitlyNullableReturnTypeHint(): void
    {
        $function = $this->indexFunction('FunctionExplicitlyNullableReturnTypeHint.phpt');

        $this->assertEquals('?string', $function->getReturnTypeHint());
        $this->assertCount(2, $function->getReturnTypes());
        $this->assertEquals('string', $function->getReturnTypes()[0]->getType());
        $this->assertEquals('string', $function->getReturnTypes()[0]->getFqcn());
        $this->assertEquals('null', $function->getReturnTypes()[1]->getType());
        $this->assertEquals('null', $function->getReturnTypes()[1]->getFqcn());
    }

    /**
     * @return void
     */
    public function testFunctionFqcnIsInCurrentNamespace(): void
    {
        $function = $this->indexFunction('FunctionFqcnInNamespace.phpt');

        $this->assertEquals('\A\foo', $function->getFqcn());
    }

    /**
     * @return void
     */
    public function testFunctionReturnTypeInDocblockIsResolved(): void
    {
        $function = $this->indexFunction('FunctionReturnTypeInDocblockIsResolved.phpt');

        $this->assertEquals('A', $function->getReturnTypes()[0]->getType());
        $this->assertEquals('\N\A', $function->getReturnTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testFunctionReturnTypeInReturnTypeHintIsResolved(): void
    {
        $function = $this->indexFunction('FunctionReturnTypeInReturnTypeHintIsResolved.phpt');

        $this->assertEquals('A', $function->getReturnTypes()[0]->getType());
        $this->assertEquals('\N\A', $function->getReturnTypes()[0]->getFqcn());
        $this->assertEquals('\N\A', $function->getReturnTypeHint());
    }

    /**
     * @return void
     */
    public function testFunctionThrows(): void
    {
        $function = $this->indexFunction('FunctionThrows.phpt');

        $this->assertCount(2, $function->getThrows());

        $this->assertEquals('A', $function->getThrows()[0]->getType());
        $this->assertEquals('\N\A', $function->getThrows()[0]->getFqcn());
        $this->assertNull($function->getThrows()[0]->getDescription());

        $this->assertEquals('\Exception', $function->getThrows()[1]->getType());
        $this->assertEquals('\Exception', $function->getThrows()[1]->getFqcn());
        $this->assertEquals('when something goes wrong.', $function->getThrows()[1]->getDescription());
    }

    /**
     * @return void
     */
    public function testFunctionSimpleParameters(): void
    {
        $function = $this->indexFunction('FunctionSimpleParameters.phpt');

        $this->assertCount(2, $function->getParameters());

        $parameter = $function->getParameters()[0];

        $this->assertEquals($function, $parameter->getFunction());
        $this->assertEquals('a', $parameter->getName());
        $this->assertNull($parameter->getTypeHint());
        $this->assertEmpty($parameter->getTypes());
        $this->assertNull($parameter->getDescription());
        $this->assertNull($parameter->getDefaultValue());
        $this->assertFalse($parameter->getIsReference());
        $this->assertFalse($parameter->getIsOptional());
        $this->assertFalse($parameter->getIsVariadic());

        $parameter = $function->getParameters()[1];

        $this->assertEquals($function, $parameter->getFunction());
        $this->assertEquals('b', $parameter->getName());
        $this->assertNull($parameter->getTypeHint());
        $this->assertEmpty($parameter->getTypes());
        $this->assertNull($parameter->getDescription());
        $this->assertNull($parameter->getDefaultValue());
        $this->assertFalse($parameter->getIsReference());
        $this->assertFalse($parameter->getIsOptional());
        $this->assertFalse($parameter->getIsVariadic());
    }

    /**
     * @return void
     */
    public function testFunctionParameterTypeHint(): void
    {
        $function = $this->indexFunction('FunctionParameterTypeHint.phpt');

        $this->assertCount(1, $function->getParameters()[0]->getTypes());
        $this->assertEquals('int', $function->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('int', $function->getParameters()[0]->getTypes()[0]->getFqcn());
        $this->assertEquals('int', $function->getParameters()[0]->getTypeHint());
    }

    /**
     * @return void
     */
    public function testFunctionParameterTypeHintIsResolved(): void
    {
        $function = $this->indexFunction('FunctionParameterTypeHintIsResolved.phpt');

        $this->assertCount(1, $function->getParameters()[0]->getTypes());
        $this->assertEquals('A', $function->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('\N\A', $function->getParameters()[0]->getTypes()[0]->getFqcn());
        $this->assertEquals('\N\A', $function->getParameters()[0]->getTypeHint());
    }

    /**
     * @return void
     */
    public function testFunctionParameterDocblockType(): void
    {
        $function = $this->indexFunction('FunctionParameterDocblockType.phpt');

        $this->assertCount(1, $function->getParameters()[0]->getTypes());
        $this->assertEquals('int', $function->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('int', $function->getParameters()[0]->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testFunctionParameterDocblockTypeIsResolved(): void
    {
        $function = $this->indexFunction('FunctionParameterDocblockTypeIsResolved.phpt');

        $this->assertCount(1, $function->getParameters()[0]->getTypes());
        $this->assertEquals('A', $function->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('\N\A', $function->getParameters()[0]->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testFunctionParameterDocblockTypeGetsPrecedenceOverTypeHint(): void
    {
        $function = $this->indexFunction('FunctionParameterDocblockTypePrecedenceOverTypeHint.phpt');

        $this->assertCount(1, $function->getParameters()[0]->getTypes());
        $this->assertEquals('int', $function->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('int', $function->getParameters()[0]->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testFunctionParameterDefaultValue(): void
    {
        $function = $this->indexFunction('FunctionParameterDefaultValue.phpt');

        $this->assertEquals('5', $function->getParameters()[0]->getDefaultValue());
    }

    /**
     * @return void
     */
    public function testFunctionParameterDefaultValueTypeDeduction(): void
    {
        $function = $this->indexFunction('FunctionParameterDefaultValueTypeDeduction.phpt');

        $this->assertCount(1, $function->getParameters()[0]->getTypes());
        $this->assertEquals('int', $function->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('int', $function->getParameters()[0]->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testFunctionParameterTypeHintGetsPrecedenceOverDefaultValueTypeDeduction(): void
    {
        $function = $this->indexFunction('FunctionParameterTypeHintGetsPrecedenceOverDefaultValueTypeDeduction.phpt');

        $this->assertCount(1, $function->getParameters()[0]->getTypes());
        $this->assertEquals('int', $function->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('int', $function->getParameters()[0]->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testFunctionParameterExplicitNullability(): void
    {
        $function = $this->indexFunction('FunctionParameterExplicitNullability.phpt');

        $this->assertEquals('?int', $function->getParameters()[0]->getTypeHint());

        $this->assertCount(2, $function->getParameters()[0]->getTypes());
        $this->assertEquals('int', $function->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('null', $function->getParameters()[0]->getTypes()[1]->getType());
    }

    /**
     * @return void
     */
    public function testFunctionParameterImplicitNullability(): void
    {
        $function = $this->indexFunction('FunctionParameterImplicitNullability.phpt');

        $this->assertEquals('int', $function->getParameters()[0]->getTypeHint());

        $this->assertCount(2, $function->getParameters()[0]->getTypes());
        $this->assertEquals('int', $function->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('null', $function->getParameters()[0]->getTypes()[1]->getType());
    }

    /**
     * @return void
     */
    public function testFunctionReferenceParameter(): void
    {
        $function = $this->indexFunction('FunctionReferenceParameter.phpt');

        $this->assertTrue($function->getParameters()[0]->getIsReference());
    }

    /**
     * @return void
     */
    public function testFunctionVariadicParameter(): void
    {
        $function = $this->indexFunction('FunctionVariadicParameter.phpt');

        $this->assertTrue($function->getParameters()[0]->getIsVariadic());

        $this->assertCount(1, $function->getParameters()[0]->getTypes());
        $this->assertEquals('int[]', $function->getParameters()[0]->getTypes()[0]->getType());
    }

    /**
     * @return void
     */
    public function testChangesArePickedUpOnReindex(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $functions = $this->container->get('managerRegistry')->getRepository(Structures\Function_::class)->findAll();

            $this->assertCount(1, $functions);
            $this->assertEquals('\foo', $functions[0]->getFqcn());

            return str_replace('foo', 'foo2 ', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $functions = $this->container->get('managerRegistry')->getRepository(Structures\Function_::class)->findAll();

            $this->assertCount(1, $functions);
            $this->assertEquals('\foo2', $functions[0]->getFqcn());
        };

        $path = $this->getPathFor('FunctionChanges.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @param string $file
     *
     * @return Structures\Function_
     */
    protected function indexFunction(string $file): Structures\Function_
    {
        $path = $this->getPathFor($file);

        $this->indexTestFile($this->container, $path);

        $functions = $this->container->get('managerRegistry')->getRepository(Structures\Function_::class)->findAll();

        $this->assertCount(1, $functions);

        return $functions[0];
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/FunctionIndexingTest/' . $file;
    }
}
