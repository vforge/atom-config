<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;

use PhpIntegrator\Analysis\Visiting\UseStatementKind;

use PhpIntegrator\Common\Position;
use PhpIntegrator\Common\FilePosition;

use PhpIntegrator\Indexing\StorageInterface;

use PhpIntegrator\NameQualificationUtilities\StructureAwareNameResolverFactoryInterface;

/**
 * Command that resolves local types in a file.
 */
class ResolveTypeCommand extends AbstractCommand
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var StructureAwareNameResolverFactoryInterface
     */
    private $structureAwareNameResolverFactory;

    /**
     * @param StorageInterface                           $storage
     * @param StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory
     */
    public function __construct(
        StorageInterface $storage,
        StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory
    ) {
        $this->storage = $storage;
        $this->structureAwareNameResolverFactory = $structureAwareNameResolverFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(ArrayAccess $arguments)
    {
        if (!isset($arguments['type'])) {
            throw new InvalidArgumentsException('The type is required for this command.');
        } elseif (!isset($arguments['file'])) {
            throw new InvalidArgumentsException('A file name is required for this command.');
        } elseif (!isset($arguments['line'])) {
            throw new InvalidArgumentsException('A line number is required for this command.');
        }

        $type = $this->resolveType(
            $arguments['type'],
            $arguments['file'],
            $arguments['line'],
            isset($arguments['kind']) ? $arguments['kind'] : UseStatementKind::TYPE_CLASSLIKE
        );

        return $type;
    }

    /**
     * Resolves the type.
     *
     * @param string $name
     * @param string $filePath
     * @param int    $line
     * @param string $kind     A constant from {@see UseStatementKind}.
     *
     * @throws InvalidArgumentsException
     *
     * @return string|null
     */
    public function resolveType(string $name, string $filePath, int $line, string $kind): ?string
    {
        $recognizedKinds = [
            UseStatementKind::TYPE_CLASSLIKE,
            UseStatementKind::TYPE_FUNCTION,
            UseStatementKind::TYPE_CONSTANT
        ];

        if (!in_array($kind, $recognizedKinds)) {
            throw new InvalidArgumentsException('Unknown kind specified!');
        }

        $file = $this->storage->getFileByPath($filePath);

        $filePosition = new FilePosition($file->getPath(), new Position($line, 0));

        return $this->structureAwareNameResolverFactory->create($filePosition)->resolve($name, $filePosition, $kind);
    }
}
