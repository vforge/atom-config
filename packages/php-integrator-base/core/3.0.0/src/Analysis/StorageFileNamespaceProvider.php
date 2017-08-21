<?php

namespace PhpIntegrator\Analysis;

use PhpIntegrator\Common\Range;
use PhpIntegrator\Common\Position;

use PhpIntegrator\Indexing\Structures;
use PhpIntegrator\Indexing\StorageInterface;

use PhpIntegrator\NameQualificationUtilities\Import;
use PhpIntegrator\NameQualificationUtilities\Namespace_;
use PhpIntegrator\NameQualificationUtilities\FileNamespaceProviderInterface;

/**
 * Provides a list of namespaces and imports for a file based on data provided by a storage provider.
 */
class StorageFileNamespaceProvider implements FileNamespaceProviderInterface
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @inheritDoc
     */
    public function provide(string $file): array
    {
        return $this->mapNamespaces($this->storage->getFileByPath($file)->getNamespaces());
    }

    /**
     * @param Structures\FileNamespace[] $namespaces
     *
     * @return Namespace_[]
     */
    protected function mapNamespaces(array $namespaces): array
    {
        $result = [];

        foreach ($namespaces as $namespace) {
            $result[] = $this->mapNamespace($namespace);
        }

        return $result;
    }

    /**
     * @param Structures\FileNamespace $namespace
     *
     * @return Namespace_
     */
    protected function mapNamespace(Structures\FileNamespace $namespace): Namespace_
    {
        $range = new Range(
            new Position($namespace->getStartLine(), 0),
            new Position($namespace->getEndLine() + 1, 0)
        );

        $imports = $this->mapImports($namespace->getImports());

        return new Namespace_($namespace->getName(), $imports, $range);
    }

    /**
     * @param Structures\FileNamespaceImport[] $imports
     *
     * @return Import[]
     */
    protected function mapImports(array $imports): array
    {
        return array_map(function (Structures\FileNamespaceImport $import): Import {
            return $this->mapImport($import);
        }, $imports);
    }

    /**
     * @param Structures\FileNamespaceImport $import
     *
     * @return Import
     */
    protected function mapImport(Structures\FileNamespaceImport $import): Import
    {
        return new Import(
            $import->getName(),
            $import->getAlias(),
            $import->getKind(),
            new Position($import->getLine(), 0)
        );
    }
}
