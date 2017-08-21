<?php

namespace PhpIntegrator\Indexing;

use LogicException;

use Doctrine\ORM;

use Doctrine\Common\Cache\Cache;

use Doctrine\Common\Persistence\AbstractManagerRegistry;

use Doctrine\DBAL\Driver\Connection;

use Doctrine\ORM\EntityManager;

use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Cache\RegionsConfiguration;
use Evenement\EventEmitterTrait;
use Evenement\EventEmitterInterface;

/**
 * Handles indexation of PHP code.
 */
class ManagerRegistry extends AbstractManagerRegistry implements EventEmitterInterface
{
    use EventEmitterTrait;

    /**
     * @var SqliteConnectionFactory
     */
    private $sqliteConnectionFactory;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $databasePath;

    /**
     * @param SqliteConnectionFactory $sqliteConnectionFactory
     * @param Cache                   $cache
     */
    public function __construct(SqliteConnectionFactory $sqliteConnectionFactory, Cache $cache)
    {
        parent::__construct(
            'managerRegistry',
            [
                'default' => 'defaultConnection'
            ],
            [
                'default' => 'defaultEntityManager'
            ],
            'default',
            'default',
            ''
        );

        $this->sqliteConnectionFactory = $sqliteConnectionFactory;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    protected function getService($name)
    {
        if ($name === 'defaultConnection') {
            return $this->getConnectionInstance();
        } elseif ($name === 'defaultEntityManager') {
            return $this->getEntityManagerInstance();
        }

        throw new LogicException('Unknown manager service requested with name ' . $name);
    }

    /**
     * @return Connection
     */
    protected function getConnectionInstance(): Connection
    {
        if ($this->connection === null) {
            $this->connection = $this->sqliteConnectionFactory->create($this->getDatabasePath());
        }

        return $this->connection;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManagerInstance(): EntityManager
    {
        if ($this->entityManager === null) {
            $regionConfig = new RegionsConfiguration();
            $cacheFactory = new DefaultCacheFactory($regionConfig, $this->cache);

            $config = ORM\Tools\Setup::createXMLMetadataConfiguration([__DIR__ . "/Structures/Mapping"], true);
            $config->setSecondLevelCacheEnabled();
            $config->getSecondLevelCacheConfiguration()->setCacheFactory($cacheFactory);

            $this->entityManager = EntityManager::create($this->getConnectionInstance(), $config);
        }

        return $this->entityManager;
    }

    /**
     * @inheritDoc
     */
    protected function resetService($name)
    {
        if ($name === 'defaultConnection') {
            if ($this->connection !== null) {
                $this->connection->close();
                $this->connection = null;
            }

            // Entity manager depends on connection, cascade reset.
            $this->resetService('defaultEntityManager');
        } elseif ($name === 'defaultEntityManager') {
            $this->entityManager = null;
        }
    }

    /**
     * @return void
     */
    public function ensureConnectionClosed(): void
    {
        $this->resetService('defaultConnection');
    }

    /**
     * @inheritDoc
     */
    public function getAliasNamespace($alias)
    {
        return $alias;
    }

    /**
     * @return string
     */
    public function getDatabasePath(): string
    {
        return $this->databasePath;
    }

    /**
     * @param string $databasePath
     *
     * @return void
     */
    public function setDatabasePath(string $databasePath): void
    {
        $this->databasePath = $databasePath;

        $this->resetService('defaultConnection');

        $this->emit(WorkspaceEventName::CHANGED, [$databasePath]);
    }

    /**
     * @return bool
     */
    public function hasInitialDatabasePathConfigured(): bool
    {
        return !!$this->databasePath;
    }
}
