<?php

namespace PhpIntegrator\Indexing;

use Evenement\EventEmitterTrait;
use Evenement\EventEmitterInterface;

/**
 * Delegates storage to another object and emits events.
 */
class EventEmittingStorage implements StorageInterface, EventEmitterInterface
{
    use EventEmitterTrait;

    /**
     * @var StorageInterface
     */
    private $delegate;

    /**
     * @param StorageInterface $delegate
     */
    public function __construct(StorageInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * @inheritDoc
     */
    public function getFiles(): array
    {
        return $this->delegate->getFiles();
    }

    /**
     * @inheritDoc
     */
    public function getAccessModifiers(): array
    {
        return $this->delegate->getAccessModifiers();
    }

    /**
     * @inheritDoc
     */
    public function findStructureByFqcn(string $fqcn): ?Structures\Structure
    {
        return $this->delegate->findStructureByFqcn($fqcn);
    }

    /**
     * @inheritDoc
     */
    public function getFileByPath(string $path): Structures\File
    {
        return $this->delegate->getFileByPath($path);
    }

    /**
     * @inheritDoc
     */
    public function persist($entity): void
    {
        $this->delegate->persist($entity);

        if ($entity instanceof Structures\FileNamespace) {
            $this->emit(IndexingEventName::NAMESPACE_UPDATED, [$entity]);
        } elseif ($entity instanceof Structures\FileNamespaceImport) {
            $this->emit(IndexingEventName::IMPORT_INSERTED);
        } elseif ($entity instanceof Structures\Constant) {
            $this->emit(IndexingEventName::CONSTANT_UPDATED, [$entity]);
        } elseif ($entity instanceof Structures\Function_) {
            $this->emit(IndexingEventName::FUNCTION_UPDATED, [$entity]);
        } elseif ($entity instanceof Structures\FunctionParameter) {
            $this->emit(IndexingEventName::FUNCTION_UPDATED, [$entity->getFunction()]);
        } elseif ($entity instanceof Structures\Structure) {
            $this->emit(IndexingEventName::STRUCTURE_UPDATED, [$entity]);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete($entity): void
    {
        $this->delegate->delete($entity);

        if ($entity instanceof Structures\FileNamespace) {
            $this->emit(IndexingEventName::NAMESPACE_REMOVED, [$entity]);
        } elseif ($entity instanceof Structures\Constant) {
            $this->emit(IndexingEventName::CONSTANT_REMOVED, [$entity]);
        } elseif ($entity instanceof Structures\Function_) {
            $this->emit(IndexingEventName::FUNCTION_REMOVED, [$entity]);
        } elseif ($entity instanceof Structures\Structure) {
            $this->emit(IndexingEventName::STRUCTURE_REMOVED, [$entity]);
        }
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction(): void
    {
        $this->delegate->beginTransaction();
    }

    /**
     * @inheritDoc
     */
    public function commitTransaction(): void
    {
        $this->delegate->commitTransaction();
    }

    /**
     * @inheritDoc
     */
    public function rollbackTransaction(): void
    {
        $this->delegate->rollbackTransaction();
    }
}
