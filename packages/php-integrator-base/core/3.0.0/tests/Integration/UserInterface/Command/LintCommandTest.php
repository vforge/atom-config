<?php

namespace PhpIntegrator\Tests\Integration\UserInterface\Command;

use PhpIntegrator\Indexing\FileNotFoundStorageException;

use PhpIntegrator\Linting\LintingSettings;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class LintCommandTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testThrowsExceptionWhenFileIsNotInIndex(): void
    {
        $command = $this->container->get('lintCommand');

        $this->expectException(FileNotFoundStorageException::class);

        $command->lint('DoesNotExist.phpt', 'Code', new LintingSettings(
            false,
            false,
            false,
            false,
            false,
            false,
            false
        ));
    }
}
