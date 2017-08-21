<?php

namespace PhpIntegrator\Indexing;

/**
 * Defines functionality that must be exposed by classes that can interact with a storage medium for persisting data
 * related to the index.
 */
interface StorageInterface
{
    /**
     * @throws StorageException
     *
     * @return Structures\File[]
     */
    public function getFiles(): array;

    /**
     * @throws StorageException
     *
     * @return Structures\AccessModifier[]
     */
    public function getAccessModifiers(): array;

    /**
     * @param string $fqcn
     *
     * @throws StorageException
     *
     * @return Structures\Structure|null
     */
    public function findStructureByFqcn(string $fqcn): ?Structures\Structure;

    /**
     * @param string $path
     *
     * @throws StorageException
     * @throws FileNotFoundStorageException
     *
     * @return Structures\File
     */
    public function getFileByPath(string $path): Structures\File;

    /**
     * @param object $entity
     *
     * @throws StorageException
     *
     * @return void
     */
    public function persist($entity): void;

    /**
     * @param object $entity
     *
     * @throws StorageException
     *
     * @return void
     */
    public function delete($entity): void;

    /**
     * @throws StorageException
     *
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * @throws StorageException
     *
     * @return void
     */
    public function commitTransaction(): void;

    /**
     * @throws StorageException
     *
     * @return void
     */
    public function rollbackTransaction(): void;
}
