<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;

use PhpIntegrator\Analysis\NamespaceListProviderInterface;
use PhpIntegrator\Analysis\FileNamespaceListProviderInterface;

use PhpIntegrator\Indexing\StorageInterface;

/**
 * Command that shows a list of available namespace.
 */
class NamespaceListCommand extends AbstractCommand
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var NamespaceListProviderInterface
     */
    private $namespaceListProvider;

    /**
     * @var FileNamespaceListProviderInterface
     */
    private $fileNamespaceListProvider;

    /**
     * @param StorageInterface                   $storage
     * @param NamespaceListProviderInterface     $namespaceListProvider
     * @param FileNamespaceListProviderInterface $fileNamespaceListProvider
     */
    public function __construct(
        StorageInterface $storage,
        NamespaceListProviderInterface $namespaceListProvider,
        FileNamespaceListProviderInterface $fileNamespaceListProvider
    ) {
        $this->storage = $storage;
        $this->namespaceListProvider = $namespaceListProvider;
        $this->fileNamespaceListProvider = $fileNamespaceListProvider;
    }

    /**
     * @inheritDoc
     */
    public function execute(ArrayAccess $arguments)
    {
        return $this->getNamespaceList($arguments['file'] ?? null);
    }

    /**
     * @param string|null $filePath
     *
     * @return array
     */
    public function getNamespaceList(?string $filePath = null): array
    {
        $criteria = [];

        if ($filePath !== null) {
            $file = $this->storage->getFileByPath($filePath);

            return $this->fileNamespaceListProvider->getAllForFile($file);
        }

        return $this->namespaceListProvider->getAll();
    }
}
