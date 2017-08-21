<?php

namespace PhpIntegrator\Tests\Integration\Tooltips;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\Indexing\Structures\AccessModifierNameValue;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class MethodIndexingTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testSimpleMethod(): void
    {
        $method = $this->indexMethod('SimpleMethod.phpt');

        $this->assertEquals('foo', $method->getName());
        $this->assertEquals($this->getPathFor('SimpleMethod.phpt'), $method->getFile()->getPath());
        $this->assertEquals(5, $method->getStartLine());
        $this->assertEquals(8, $method->getEndLine());
        $this->assertFalse($method->getIsDeprecated());
        $this->assertNull($method->getShortDescription());
        $this->assertNull($method->getLongDescription());
        $this->assertNull($method->getReturnDescription());
        $this->assertNull($method->getReturnTypeHint());
        $this->assertFalse($method->getHasDocblock());
        $this->assertEmpty($method->getThrows());
        $this->assertEmpty($method->getParameters());
        $this->assertEmpty($method->getReturnTypes());
        $this->assertFalse($method->getIsMagic());
        $this->assertFalse($method->getIsStatic());
        $this->assertFalse($method->getIsAbstract());
        $this->assertFalse($method->getIsFinal());
        $this->assertEquals(AccessModifierNameValue::PUBLIC_, $method->getAccessModifier()->getName());
    }

    /**
     * @return void
     */
    public function testDeprecatedMethod(): void
    {
        $method = $this->indexMethod('DeprecatedMethod.phpt');

        $this->assertTrue($method->getIsDeprecated());
    }

    /**
     * @return void
     */
    public function testMethodShortDescription(): void
    {
        $method = $this->indexMethod('MethodShortDescription.phpt');

        $this->assertEquals('This is a summary.', $method->getShortDescription());
    }

    /**
     * @return void
     */
    public function testMethodLongDescription(): void
    {
        $method = $this->indexMethod('MethodLongDescription.phpt');

        $this->assertEquals('This is a long description.', $method->getLongDescription());
    }

    /**
     * @return void
     */
    public function testMethodReturnDescription(): void
    {
        $method = $this->indexMethod('MethodReturnDescription.phpt');

        $this->assertEquals('This is a return description.', $method->getReturnDescription());
    }

    /**
     * @return void
     */
    public function testMethodReturnTypeIsFetchedFromDocblockAndGetsPrecedenceOverReturnTypeHint(): void
    {
        $method = $this->indexMethod('MethodReturnTypeFromDocblock.phpt');

        $this->assertEquals('int', $method->getReturnTypes()[0]->getType());
        $this->assertEquals('int', $method->getReturnTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testMethodReturnTypeIsFetchedFromReturnTypeHint(): void
    {
        $method = $this->indexMethod('MethodReturnTypeFromTypeHint.phpt');

        $this->assertEquals('string', $method->getReturnTypes()[0]->getType());
        $this->assertEquals('string', $method->getReturnTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testMethodReturnTypeHint(): void
    {
        $method = $this->indexMethod('MethodReturnTypeHint.phpt');

        $this->assertEquals('string', $method->getReturnTypeHint());
    }

    /**
     * @return void
     */
    public function testMethodExplicitlyNullableReturnTypeHint(): void
    {
        $method = $this->indexMethod('MethodExplicitlyNullableReturnTypeHint.phpt');

        $this->assertEquals('?string', $method->getReturnTypeHint());
        $this->assertCount(2, $method->getReturnTypes());
        $this->assertEquals('string', $method->getReturnTypes()[0]->getType());
        $this->assertEquals('string', $method->getReturnTypes()[0]->getFqcn());
        $this->assertEquals('null', $method->getReturnTypes()[1]->getType());
        $this->assertEquals('null', $method->getReturnTypes()[1]->getFqcn());
    }

    /**
     * @return void
     */
    public function testMethodReturnTypeInDocblockIsResolved(): void
    {
        $method = $this->indexMethod('MethodReturnTypeInDocblockIsResolved.phpt');

        $this->assertEquals('A', $method->getReturnTypes()[0]->getType());
        $this->assertEquals('\N\A', $method->getReturnTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testMethodReturnTypeInReturnTypeHintIsResolved(): void
    {
        $method = $this->indexMethod('MethodReturnTypeInReturnTypeHintIsResolved.phpt');

        $this->assertEquals('A', $method->getReturnTypes()[0]->getType());
        $this->assertEquals('\N\A', $method->getReturnTypes()[0]->getFqcn());
        $this->assertEquals('\N\A', $method->getReturnTypeHint());
    }

    /**
     * @return void
     */
    public function testMethodThrows(): void
    {
        $method = $this->indexMethod('MethodThrows.phpt');

        $this->assertCount(2, $method->getThrows());

        $this->assertEquals('A', $method->getThrows()[0]->getType());
        $this->assertEquals('\N\A', $method->getThrows()[0]->getFqcn());
        $this->assertNull($method->getThrows()[0]->getDescription());

        $this->assertEquals('\Exception', $method->getThrows()[1]->getType());
        $this->assertEquals('\Exception', $method->getThrows()[1]->getFqcn());
        $this->assertEquals('when something goes wrong.', $method->getThrows()[1]->getDescription());
    }

    /**
     * @return void
     */
    public function testMethodSimpleParameters(): void
    {
        $method = $this->indexMethod('MethodSimpleParameters.phpt');

        $this->assertCount(2, $method->getParameters());

        $parameter = $method->getParameters()[0];

        $this->assertEquals($method, $parameter->getMethod());
        $this->assertEquals('a', $parameter->getName());
        $this->assertNull($parameter->getTypeHint());
        $this->assertEmpty($parameter->getTypes());
        $this->assertNull($parameter->getDescription());
        $this->assertNull($parameter->getDefaultValue());
        $this->assertFalse($parameter->getIsReference());
        $this->assertFalse($parameter->getIsOptional());
        $this->assertFalse($parameter->getIsVariadic());

        $parameter = $method->getParameters()[1];

        $this->assertEquals($method, $parameter->getMethod());
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
    public function testMethodParameterTypeHint(): void
    {
        $method = $this->indexMethod('MethodParameterTypeHint.phpt');

        $this->assertCount(1, $method->getParameters()[0]->getTypes());
        $this->assertEquals('int', $method->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('int', $method->getParameters()[0]->getTypes()[0]->getFqcn());
        $this->assertEquals('int', $method->getParameters()[0]->getTypeHint());
    }

    /**
     * @return void
     */
    public function testMethodParameterTypeHintIsResolved(): void
    {
        $method = $this->indexMethod('MethodParameterTypeHintIsResolved.phpt');

        $this->assertCount(1, $method->getParameters()[0]->getTypes());
        $this->assertEquals('A', $method->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('\N\A', $method->getParameters()[0]->getTypes()[0]->getFqcn());
        $this->assertEquals('\N\A', $method->getParameters()[0]->getTypeHint());
    }

    /**
     * @return void
     */
    public function testMethodParameterDocblockType(): void
    {
        $method = $this->indexMethod('MethodParameterDocblockType.phpt');

        $this->assertCount(1, $method->getParameters()[0]->getTypes());
        $this->assertEquals('int', $method->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('int', $method->getParameters()[0]->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testMethodParameterDocblockTypeIsResolved(): void
    {
        $method = $this->indexMethod('MethodParameterDocblockTypeIsResolved.phpt');

        $this->assertCount(1, $method->getParameters()[0]->getTypes());
        $this->assertEquals('A', $method->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('\N\A', $method->getParameters()[0]->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testMethodParameterDocblockTypeGetsPrecedenceOverTypeHint(): void
    {
        $method = $this->indexMethod('MethodParameterDocblockTypePrecedenceOverTypeHint.phpt');

        $this->assertCount(1, $method->getParameters()[0]->getTypes());
        $this->assertEquals('int', $method->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('int', $method->getParameters()[0]->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testMethodParameterDefaultValue(): void
    {
        $method = $this->indexMethod('MethodParameterDefaultValue.phpt');

        $this->assertEquals('5', $method->getParameters()[0]->getDefaultValue());
    }

    /**
     * @return void
     */
    public function testMethodParameterDefaultValueTypeDeduction(): void
    {
        $method = $this->indexMethod('MethodParameterDefaultValueTypeDeduction.phpt');

        $this->assertCount(1, $method->getParameters()[0]->getTypes());
        $this->assertEquals('int', $method->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('int', $method->getParameters()[0]->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testMethodParameterTypeHintGetsPrecedenceOverDefaultValueTypeDeduction(): void
    {
        $method = $this->indexMethod('MethodParameterTypeHintGetsPrecedenceOverDefaultValueTypeDeduction.phpt');

        $this->assertCount(1, $method->getParameters()[0]->getTypes());
        $this->assertEquals('int', $method->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('int', $method->getParameters()[0]->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testMethodParameterExplicitNullability(): void
    {
        $method = $this->indexMethod('MethodParameterExplicitNullability.phpt');;

        $this->assertEquals('?int', $method->getParameters()[0]->getTypeHint());

        $this->assertCount(2, $method->getParameters()[0]->getTypes());
        $this->assertEquals('int', $method->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('null', $method->getParameters()[0]->getTypes()[1]->getType());
    }

    /**
     * @return void
     */
    public function testMethodParameterImplicitNullability(): void
    {
        $method = $this->indexMethod('MethodParameterImplicitNullability.phpt');

        $this->assertEquals('int', $method->getParameters()[0]->getTypeHint());

        $this->assertCount(2, $method->getParameters()[0]->getTypes());
        $this->assertEquals('int', $method->getParameters()[0]->getTypes()[0]->getType());
        $this->assertEquals('null', $method->getParameters()[0]->getTypes()[1]->getType());
    }

    /**
     * @return void
     */
    public function testMethodReferenceParameter(): void
    {
        $method = $this->indexMethod('MethodReferenceParameter.phpt');

        $this->assertTrue($method->getParameters()[0]->getIsReference());
    }

    /**
     * @return void
     */
    public function testMethodVariadicParameter(): void
    {
        $method = $this->indexMethod('MethodVariadicParameter.phpt');

        $this->assertTrue($method->getParameters()[0]->getIsVariadic());

        $this->assertCount(1, $method->getParameters()[0]->getTypes());
        $this->assertEquals('int[]', $method->getParameters()[0]->getTypes()[0]->getType());
    }

    /**
     * @return void
     */
    public function testFinalMethod(): void
    {
        $method = $this->indexMethod('FinalMethod.phpt');

        $this->assertTrue($method->getIsFinal());
    }

    /**
     * @return void
     */
    public function testAbstractMethod(): void
    {
        $method = $this->indexMethod('AbstractMethod.phpt');

        $this->assertTrue($method->getIsAbstract());
    }

    /**
     * @return void
     */
    public function testMagicMethod(): void
    {
        $method = $this->indexMethod('MagicMethod.phpt');

        $this->assertTrue($method->getIsMagic());
        $this->assertFalse($method->getIsStatic());
    }

    /**
     * @return void
     */
    public function testMagicMethodWithReturnType(): void
    {
        $method = $this->indexMethod('MagicMethodWithReturnType.phpt');

        $this->assertCount(1, $method->getReturnTypes());
        $this->assertEquals('int', $method->getReturnTypes()[0]->getType());
        $this->assertEquals('int', $method->getReturnTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testMagicMethodReturnTypeIsResolved(): void
    {
        $method = $this->indexMethod('MagicMethodReturnTypeIsResolved.phpt');

        $this->assertCount(1, $method->getReturnTypes());
        $this->assertEquals('A', $method->getReturnTypes()[0]->getType());
        $this->assertEquals('\N\A', $method->getReturnTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testMagicMethodWithDescription(): void
    {
        $method = $this->indexMethod('MagicMethodWithDescription.phpt');

        $this->assertEquals('A summary.', $method->getShortDescription());
    }

    /**
     * @return void
     */
    public function testMagicMethodOmittingReturnType(): void
    {
        $method = $this->indexMethod('MagicMethodOmittingReturnType.phpt');

        $this->assertCount(1, $method->getReturnTypes());
        $this->assertEquals('void', $method->getReturnTypes()[0]->getType());
        $this->assertEquals('void', $method->getReturnTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testMagicMethodWithDescriptionWithoutReturnType(): void
    {
        $method = $this->indexMethod('MagicMethodWithDescriptionWithoutReturnType.phpt');

        $this->assertEquals('A summary.', $method->getShortDescription());

        $this->assertCount(1, $method->getReturnTypes());
        $this->assertEquals('void', $method->getReturnTypes()[0]->getType());
        $this->assertEquals('void', $method->getReturnTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testStaticMagicMethodWithReturnType(): void
    {
        $method = $this->indexMethod('StaticMagicMethodWithReturnType.phpt');

        $this->assertTrue($method->getIsStatic());
    }

    /**
     * @return void
     */
    public function testStaticMagicMethodWithoutReturnType(): void
    {
        $method = $this->indexMethod('StaticMagicMethodWithoutReturnType.phpt');

        $this->assertTrue($method->getIsStatic());
    }

    /**
     * @return void
     */
    public function testMagicMethodWithRequiredParameter(): void
    {
        $method = $this->indexMethod('MagicMethodWithRequiredParameter.phpt');

        $this->assertCount(1, $method->getParameters());

        $parameter = $method->getParameters()[0];

        $this->assertEquals($method, $parameter->getMethod());
        $this->assertEquals('a', $parameter->getName());
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
    public function testMagicMethodWithOptionalParameter(): void
    {
        $method = $this->indexMethod('MagicMethodWithOptionalParameter.phpt');

        $this->assertCount(1, $method->getParameters());

        $parameter = $method->getParameters()[0];

        $this->assertEquals($method, $parameter->getMethod());
        $this->assertEquals('a', $parameter->getName());
        $this->assertNull($parameter->getTypeHint());
        $this->assertEmpty($parameter->getTypes());
        $this->assertNull($parameter->getDescription());
        $this->assertNull($parameter->getDefaultValue());
        $this->assertFalse($parameter->getIsReference());
        $this->assertTrue($parameter->getIsOptional());
        $this->assertFalse($parameter->getIsVariadic());
    }

    /**
     * @return void
     */
    public function testMagicMethodParameterTypeIsResolved(): void
    {
        $method = $this->indexMethod('MagicMethodParameterTypeIsResolved.phpt');

        $this->assertCount(1, $method->getParameters());

        $parameter = $method->getParameters()[0];

        $this->assertCount(1, $parameter->getTypes());
        $this->assertEquals('A', $parameter->getTypes()[0]->getType());
        $this->assertEquals('\N\A', $parameter->getTypes()[0]->getFqcn());
    }

    /**
     * @return void
     */
    public function testStaticMethod(): void
    {
        $method = $this->indexMethod('StaticMethod.phpt');

        $this->assertTrue($method->getIsStatic());
    }

    /**
     * @return void
     */
    public function testImplicitlyPublicMethod(): void
    {
        $method = $this->indexMethod('ImplicitlyPublicMethod.phpt');

        $this->assertEquals(AccessModifierNameValue::PUBLIC_, $method->getAccessModifier()->getName());
    }

    /**
     * @return void
     */
    public function testExplicitlyPublicMethod(): void
    {
        $method = $this->indexMethod('ExplicitlyPublicMethod.phpt');

        $this->assertEquals(AccessModifierNameValue::PUBLIC_, $method->getAccessModifier()->getName());
    }

    /**
     * @return void
     */
    public function testProtectedMethod(): void
    {
        $method = $this->indexMethod('ProtectedMethod.phpt');

        $this->assertEquals(AccessModifierNameValue::PROTECTED_, $method->getAccessModifier()->getName());
    }

    /**
     * @return void
     */
    public function testPrivateMethod(): void
    {
        $method = $this->indexMethod('PrivateMethod.phpt');

        $this->assertEquals(AccessModifierNameValue::PRIVATE_, $method->getAccessModifier()->getName());
    }

    /**
     * @return void
     */
    public function testChangesArePickedUpOnReindex(): void
    {
        $afterIndex = function (ContainerBuilder $container, string $path, string $source) {
            $classes = $this->container->get('managerRegistry')->getRepository(Structures\Class_::class)->findAll();

            $this->assertCount(1, $classes);
            $this->assertCount(1, $classes[0]->getMethods());

            $method = $classes[0]->getMethods()[0];

            $this->assertEquals('foo', $method->getName());

            return str_replace('foo', 'foo2 ', $source);
        };

        $afterReindex = function (ContainerBuilder $container, string $path, string $source) {
            $classes = $this->container->get('managerRegistry')->getRepository(Structures\Class_::class)->findAll();

            $this->assertCount(1, $classes);
            $this->assertCount(1, $classes[0]->getMethods());

            $method = $classes[0]->getMethods()[0];

            $this->assertEquals('foo2', $method->getName());
        };

        $path = $this->getPathFor('MethodChanges.phpt');

        $this->assertReindexingChanges($path, $afterIndex, $afterReindex);
    }

    /**
     * @param string $file
     *
     * @return Structures\Method
     */
    protected function indexMethod(string $file): Structures\Method
    {
        $path = $this->getPathFor($file);

        $this->indexTestFile($this->container, $path);

        $classes = $this->container->get('managerRegistry')->getRepository(Structures\Class_::class)->findAll();

        $this->assertCount(1, $classes);
        $this->assertCount(1, $classes[0]->getMethods());

        $method = $classes[0]->getMethods()[0];

        $this->assertEquals($classes[0], $method->getStructure());

        return $method;
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/MethodIndexingTest/' . $file;
    }
}
