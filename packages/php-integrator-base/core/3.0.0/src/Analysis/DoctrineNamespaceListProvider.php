<?php

namespace PhpIntegrator\Analysis;

use RuntimeException;

use Doctrine\DBAL\Exception\DriverException;

use PhpIntegrator\Analysis\Conversion\NamespaceConverter;

use PhpIntegrator\Indexing\Structures;
use PhpIntegrator\Indexing\ManagerRegistry;

/**
 * Retrieves a list of available structures via Doctrine.
 */
class DoctrineNamespaceListProvider implements FileNamespaceListProviderInterface, NamespaceListProviderInterface
{
    /**
     * @var NamespaceConverter
     */
    private $namespaceConverter;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @param NamespaceConverter $namespaceConverter
     * @param ManagerRegistry    $managerRegistry
     */
    public function __construct(NamespaceConverter $namespaceConverter, ManagerRegistry $managerRegistry)
    {
        $this->namespaceConverter = $namespaceConverter;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        try {
            $namespaces = $this->managerRegistry->getRepository(Structures\FileNamespace::class)->findAll();
        } catch (DriverException $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        return array_map(function (Structures\FileNamespace $namespace) {
            return $this->namespaceConverter->convert($namespace);
        }, $namespaces);
    }

    /**
     * @inheritDoc
     */
    public function getAllForFile(Structures\File $file): array
    {
        try {
            $namespaces = $this->managerRegistry->getRepository(Structures\FileNamespace::class)->findBy([
                'file' => $file
            ]);
        } catch (DriverException $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        return array_map(function (Structures\FileNamespace $namespace) {
            return $this->namespaceConverter->convert($namespace);
        }, $namespaces);
    }
}
