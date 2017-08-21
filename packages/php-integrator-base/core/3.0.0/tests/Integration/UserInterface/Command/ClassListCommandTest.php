<?php

namespace PhpIntegrator\Tests\Integration\UserInterface\Command;

use PhpIntegrator\Indexing\FileNotFoundStorageException;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class ClassListCommandTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testThrowsExceptionWhenFileIsNotInIndex(): void
    {
        $command = $this->container->get('classListCommand');

        $this->expectException(FileNotFoundStorageException::class);

        $command->getAllForFilePath('DoesNotExist.phpt');
    }
}
