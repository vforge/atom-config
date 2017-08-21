<?php

namespace PhpIntegrator\Tests\Integration\UserInterface\Command;

use PhpIntegrator\Analysis\Visiting\UseStatementKind;

use PhpIntegrator\Indexing\FileNotFoundStorageException;
use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class ResolveTypeCommandTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testCorrectlyResolvesVariousTypes(): void
    {
        $path = __DIR__ . '/ResolveTypeCommandTest/' . 'ResolveType.phpt';

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('resolveTypeCommand');

        $this->assertEquals('\C', $command->resolveType('C', $path, 1, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('\A\C', $command->resolveType('C', $path, 5, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('\B\C', $command->resolveType('C', $path, 10, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('\B\DateTime', $command->resolveType('DateTime', $path, 10, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('\DateTime', $command->resolveType('DateTime', $path, 11, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('\DateTime', $command->resolveType('DateTime', $path, 12, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('\C\D\Test', $command->resolveType('D\Test', $path, 13, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('\DateTime', $command->resolveType('DateTime', $path, 18, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('\DateTime', $command->resolveType('DateTime', $path, 18, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('\A\SOME_CONSTANT', $command->resolveType('SOME_CONSTANT', $path, 20, UseStatementKind::TYPE_CONSTANT));
        $this->assertEquals('\A\some_function', $command->resolveType('some_function', $path, 20, UseStatementKind::TYPE_FUNCTION));
    }

    /**
     * @return void
     */
    public function testCorrectlyResolvesUnqualifiedConstantsWhenNotInNamespace(): void
    {
        $path = __DIR__ . '/ResolveTypeCommandTest/' . 'UnqualifiedConstant.phpt';

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('resolveTypeCommand');

        $this->assertEquals('\SOME_CONSTANT', $command->resolveType('SOME_CONSTANT', $path, 2, UseStatementKind::TYPE_CONSTANT));
    }

    /**
     * @return void
     */
    public function testCorrectlyResolvesUnqualifiedConstantsWhenInNamespaceAndNoConstantRelativeToTheNamespaceExists(): void
    {
        $path = __DIR__ . '/ResolveTypeCommandTest/' . 'UnqualifiedConstant.phpt';

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('resolveTypeCommand');

        $this->assertEquals('\SOME_ROOT_CONSTANT', $command->resolveType('SOME_ROOT_CONSTANT', $path, 6, UseStatementKind::TYPE_CONSTANT));
    }

    /**
     * @return void
     */
    public function testCorrectlyResolvesUnqualifiedConstantsWhenInNamespaceAndConstantRelativeToTheNamespaceExists(): void
    {
        $path = __DIR__ . '/ResolveTypeCommandTest/' . 'UnqualifiedConstant.phpt';

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('resolveTypeCommand');

        $this->assertEquals('\A\SOME_CONSTANT', $command->resolveType('SOME_CONSTANT', $path, 6, UseStatementKind::TYPE_CONSTANT));
    }

    /**
     * @return void
     */
    public function testCorrectlyResolvesUnqualifiedFunctionsWhenNotInNamespace(): void
    {
        $path = __DIR__ . '/ResolveTypeCommandTest/' . 'UnqualifiedFunction.phpt';

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('resolveTypeCommand');

        $this->assertEquals('\some_function', $command->resolveType('some_function', $path, 2, UseStatementKind::TYPE_FUNCTION));
    }

    /**
     * @return void
     */
    public function testCorrectlyResolvesUnqualifiedFunctionsWhenInNamespaceAndNoFunctionRelativeToTheNamespaceExists(): void
    {
        $path = __DIR__ . '/ResolveTypeCommandTest/' . 'UnqualifiedFunction.phpt';

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('resolveTypeCommand');

        $this->assertEquals('\some_root_function', $command->resolveType('some_root_function', $path, 6, UseStatementKind::TYPE_FUNCTION));
    }

    /**
     * @return void
     */
    public function testCorrectlyResolvesUnqualifiedFunctionsWhenInNamespaceAndFunctionRelativeToTheNamespaceExists(): void
    {
        $path = __DIR__ . '/ResolveTypeCommandTest/' . 'UnqualifiedFunction.phpt';

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('resolveTypeCommand');

        $this->assertEquals('\A\some_function', $command->resolveType('some_function', $path, 6, UseStatementKind::TYPE_FUNCTION));
    }

    /**
     * @return void
     */
    public function testCorrectlyIgnoresMismatchedKinds(): void
    {
        $path = __DIR__ . '/ResolveTypeCommandTest/' . 'ResolveType.phpt';

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('resolveTypeCommand');

        $this->assertEquals('\SOME_CONSTANT', $command->resolveType('SOME_CONSTANT', $path, 20, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('\some_function', $command->resolveType('some_function', $path, 20, UseStatementKind::TYPE_CLASSLIKE));
    }

    /**
     * @return void
     */
    public function testThrowsExceptionWhenFileIsNotInIndex(): void
    {
        $command = $this->container->get('resolveTypeCommand');

        $this->expectException(FileNotFoundStorageException::class);

        $command->resolveType('A', 'DoesNotExist.phpt', 1, UseStatementKind::TYPE_CLASSLIKE);
    }
}
