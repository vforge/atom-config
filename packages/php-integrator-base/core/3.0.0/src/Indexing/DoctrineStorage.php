<?php

namespace PhpIntegrator\Indexing;

use LogicException;

use Doctrine\Common\Persistence\ManagerRegistry;

use Doctrine\DBAL\Exception\DriverException;

use PhpIntegrator\Analysis\MetadataProviderInterface;

/**
 * Storage backend that uses Doctrine.
 */
class DoctrineStorage implements StorageInterface, MetadataProviderInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function getFiles(): array
    {
        try {
            return $this->managerRegistry->getRepository(Structures\File::class)->findAll();
        } catch (DriverException $e) {
            throw new StorageException($e->getMessage(), 0, $e);
        }

        throw new LogicException('Should never be reached');
    }

    /**
     * @inheritDoc
     */
    public function getAccessModifiers(): array
    {
        try {
            return $this->managerRegistry->getRepository(Structures\AccessModifier::class)->findAll();
        } catch (DriverException $e) {
            throw new StorageException($e->getMessage(), 0, $e);
        }

        throw new LogicException('Should never be reached');
    }

    /**
     * @inheritDoc
     */
    public function findStructureByFqcn(string $fqcn): ?Structures\Structure
    {
        try {
            return $this->managerRegistry->getRepository(Structures\Structure::class)->findOneBy([
                'fqcn' => $fqcn
            ]);
        } catch (DriverException $e) {
            throw new StorageException($e->getMessage(), 0, $e);
        }

        throw new LogicException('Should never be reached');
    }

    /**
     * @inheritDoc
     */
    public function getFileByPath(string $path): Structures\File
    {
        try {
            $file = $this->managerRegistry->getRepository(Structures\File::class)->findOneBy([
                'path' => $path
            ]);
        } catch (DriverException $e) {
            throw new StorageException($e->getMessage(), 0, $e);
        }

        if ($file === null) {
            throw new FileNotFoundStorageException("Could not find file \"{$path}\" in index");
        }

        return $file;
    }

    /**
     * @inheritDoc
     */
    public function persist($entity): void
    {
        try {
            $this->managerRegistry->getManager()->persist($entity);
            $this->managerRegistry->getManager()->flush();
        } catch (DriverException $e) {
            throw new StorageException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete($entity): void
    {
        try {
            $this->managerRegistry->getManager()->remove($entity);
        } catch (DriverException $e) {
            throw new StorageException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction(): void
    {
        try {
            $this->managerRegistry->getConnection()->beginTransaction();
        } catch (DriverException $e) {
            throw new StorageException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function commitTransaction(): void
    {
        try {
            $this->managerRegistry->getManager()->flush();

            $this->managerRegistry->getConnection()->commit();
        } catch (DriverException $e) {
            throw new StorageException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function rollbackTransaction(): void
    {
        try {
            $this->managerRegistry->getConnection()->rollback();
        } catch (DriverException $e) {
            throw new StorageException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getMetaStaticMethodTypesFor(string $fqcn, string $method): array
    {
        try {
            return $this->managerRegistry->getRepository(Structures\MetaStaticMethodType::class)->findBy([
                'fqcn' => $fqcn,
                'name' => $method
            ]);
        } catch (DriverException $e) {
            throw new StorageException($e->getMessage(), 0, $e);
        }

        throw new LogicException('Should never be reached');
    }
}
