<?php

namespace PhpIntegrator\Tests\Integration\UserInterface\Command;

use PhpIntegrator\Analysis\Visiting\UseStatementKind;

use PhpIntegrator\Indexing\FileNotFoundStorageException;
use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class LocalizeTypeCommandTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testCorrectlyLocalizesVariousTypes(): void
    {
        $path = __DIR__ . '/LocalizeTypeCommandTest/' . 'LocalizeType.phpt';

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('localizeTypeCommand');

        $this->assertEquals('C', $command->localizeType('C', $path, 1, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('\C', $command->localizeType('\C', $path, 5, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('C', $command->localizeType('\A\C', $path, 5, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('C', $command->localizeType('\B\C', $path, 10, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('DateTime', $command->localizeType('\B\DateTime', $path, 10, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('DateTime', $command->localizeType('\DateTime', $path, 11, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('DateTime', $command->localizeType('DateTime', $path, 12, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('DateTime', $command->localizeType('\DateTime', $path, 12, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('D\Test', $command->localizeType('\C\D\Test', $path, 13, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('E', $command->localizeType('\C\D\E', $path, 14, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('H', $command->localizeType('\F\G\H', $path, 16, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('SOME_CONSTANT', $command->localizeType('\A\SOME_CONSTANT', $path, 18, UseStatementKind::TYPE_CONSTANT));
        $this->assertEquals('some_function', $command->localizeType('\A\some_function', $path, 18, UseStatementKind::TYPE_FUNCTION));
    }

    /**
     * @return void
     */
    public function testCorrectlyIgnoresMismatchedKinds(): void
    {
        $path = __DIR__ . '/LocalizeTypeCommandTest/' . 'LocalizeType.phpt';

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('localizeTypeCommand');

        $this->assertEquals('\C\D\Test', $command->localizeType('\C\D\Test', $path, 13, UseStatementKind::TYPE_CONSTANT));
        $this->assertEquals('\SOME_CONSTANT', $command->localizeType('\SOME_CONSTANT', $path, 18, UseStatementKind::TYPE_CLASSLIKE));
        $this->assertEquals('\some_function', $command->localizeType('\some_function', $path, 18, UseStatementKind::TYPE_CLASSLIKE));
    }

    /**
     * @return void
     */
    public function testThrowsExceptionWhenFileIsNotInIndex(): void
    {
        $command = $this->container->get('localizeTypeCommand');

        $this->expectException(FileNotFoundStorageException::class);

        $command->localizeType('A', 'DoesNotExist.phpt', 1, UseStatementKind::TYPE_CLASSLIKE);
    }
}
