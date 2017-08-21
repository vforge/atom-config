<?php

namespace PhpIntegrator\Tests\Integration\UserInterface\Command;

use PhpIntegrator\Indexing\FileNotFoundStorageException;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class SignatureHelpCommandTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testThrowsExceptionWhenFileIsNotInIndex(): void
    {
        $command = $this->container->get('signatureHelpCommand');

        $this->expectException(FileNotFoundStorageException::class);

        $command->signatureHelp('DoesNotExist.phpt', 'Code', 1);
    }
}
