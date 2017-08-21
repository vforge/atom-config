<?php

namespace PhpIntegrator\Tests\Integration\Analysis;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

/**
 * Contains tests that test whether the registry properly interacts with workspace changes.
 */
class FunctionListRegistryWorkspaceInteractionTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testRegistryIsClearedWhenWorkspaceChanges(): void
    {
        $registry = $this->container->get('functionListProvider.registry');

        $this->assertEmpty($registry->getAll());

        $registry->add([
            'fqcn' => '\Test'
        ]);

        $this->assertCount(1, $registry->getAll());

        $this->container->get('managerRegistry')->setDatabasePath(':memory:');
        $this->container->get('initializeCommand')->initialize(false);

        $this->assertEmpty($registry->getAll());
    }
}
