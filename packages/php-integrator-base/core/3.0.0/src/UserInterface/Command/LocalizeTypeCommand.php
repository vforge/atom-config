<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;

use PhpIntegrator\Analysis\Visiting\UseStatementKind;

use PhpIntegrator\Common\Position;
use PhpIntegrator\Common\FilePosition;

use PhpIntegrator\Indexing\StorageInterface;

use PhpIntegrator\NameQualificationUtilities\PositionalNameLocalizerFactoryInterface;

/**
 * Command that makes a FQCN relative to local use statements in a file.
 */
class LocalizeTypeCommand extends AbstractCommand
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var PositionalNameLocalizerFactoryInterface
     */
    private $positionalNameLocalizerFactory;

    /**
     * @param StorageInterface                        $storage
     * @param PositionalNameLocalizerFactoryInterface $positionalNameLocalizerFactory
     */
    public function __construct(
        StorageInterface $storage,
        PositionalNameLocalizerFactoryInterface $positionalNameLocalizerFactory
    ) {
        $this->storage = $storage;
        $this->positionalNameLocalizerFactory = $positionalNameLocalizerFactory;
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

        $type = $this->localizeType(
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
     * @param string $type
     * @param string $filePath
     * @param int    $line
     * @param string $kind     A constant from {@see UseStatementKind}.
     *
     * @return string|null
     */
    public function localizeType(string $type, string $filePath, int $line, string $kind): ?string
    {
        $file = $this->storage->getFileByPath($filePath);

        $filePosition = new FilePosition($file->getPath(), new Position($line, 0));

        return $this->positionalNameLocalizerFactory->create($filePosition)->localize($type, $kind);
    }
}
