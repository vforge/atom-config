<?php

namespace PhpIntegrator\Tests\Integration\UserInterface\Command;

use PhpIntegrator\Indexing\FileNotFoundStorageException;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class TooltipCommandTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testThrowsExceptionWhenFileIsNotInIndex(): void
    {
        $command = $this->container->get('tooltipCommand');

        $this->expectException(FileNotFoundStorageException::class);

        $command->getTooltip('DoesNotExist.phpt', 'Code', 1);
    }
}
