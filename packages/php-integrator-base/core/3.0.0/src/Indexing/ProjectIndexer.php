<?php

namespace PhpIntegrator\Indexing;

use UnexpectedValueException;

use PhpIntegrator\Utility\SourceCodeStreamReader;

/**
 * Handles project and folder indexing.
 */
class ProjectIndexer
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var FileIndexerInterface
     */
    private $fileIndexer;

    /**
     * @var SourceCodeStreamReader
     */
    private $sourceCodeStreamReader;

    /**
     * @var callable|null
     */
    private $progressStreamingCallback;

    /**
     * @param StorageInterface       $storage
     * @param FileIndexerInterface   $fileIndexer
     * @param SourceCodeStreamReader $sourceCodeStreamReader
     */
    public function __construct(
        StorageInterface $storage,
        FileIndexerInterface $fileIndexer,
        SourceCodeStreamReader $sourceCodeStreamReader
    ) {
        $this->storage = $storage;
        $this->fileIndexer = $fileIndexer;
        $this->sourceCodeStreamReader = $sourceCodeStreamReader;
    }

    /**
     * @return callable|null
     */
    public function getProgressStreamingCallback(): ?callable
    {
        return $this->progressStreamingCallback;
    }

    /**
     * @param callable|null $progressStreamingCallback
     *
     * @return static
     */
    public function setProgressStreamingCallback(?callable $progressStreamingCallback)
    {
        $this->progressStreamingCallback = $progressStreamingCallback;
        return $this;
    }

    /**
     * Logs progress for streaming progress.
     *
     * @param int $itemNumber
     * @param int $totalItemCount
     *
     * @return void
     */
    protected function sendProgress(int $itemNumber, int $totalItemCount): void
    {
        $callback = $this->getProgressStreamingCallback();

        if (!$callback) {
            return;
        }

        if ($totalItemCount) {
            $progress = ($itemNumber / $totalItemCount) * 100;
        } else {
            $progress = 100;
        }

        $callback($progress);
    }

    /**
     * Indexes the specified project.
     *
     * @param string[] $items
     * @param string[] $extensionsToIndex
     * @param string[] $excludedPaths
     * @param array    $sourceOverrideMap
     *
     * @return void
     */
    public function index(
        array $items,
        array $extensionsToIndex,
        array $excludedPaths = [],
        array $sourceOverrideMap = []
    ): void {
        $fileModifiedMap = $this->getFileModifiedMap();

        // The modification time doesn't matter for files we have direct source code for, as this source code always
        // needs to be indexed (e.g it may simply not have been saved to disk yet).
        foreach ($sourceOverrideMap as $filePath => $source) {
            unset($fileModifiedMap[$filePath]);
        }

        $iterator = new Iterating\MultiRecursivePathIterator($items);
        $iterator = new Iterating\ExtensionFilterIterator($iterator, $extensionsToIndex);
        $iterator = new Iterating\ExclusionFilterIterator($iterator, $excludedPaths);
        $iterator = new Iterating\ModificationTimeFilterIterator($iterator, $fileModifiedMap);

        $totalItems = iterator_count($iterator);

        $this->sendProgress(0, $totalItems);

        $i = 0;

        /** @var \SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            $filePath = $fileInfo->getPathname();

            $code = null;

            if (isset($sourceOverrideMap[$filePath])) {
                $code = $sourceOverrideMap[$filePath];
            } else {
                try {
                    $code = $this->sourceCodeStreamReader->getSourceCodeFromFile($filePath);
                } catch (UnexpectedValueException $e) {
                    $code = null; // Skip files that we can't read.
                }
            }

            if ($code !== null) {
                try {
                    $this->fileIndexer->index($filePath, $code);
                } catch (IndexingFailedException $e) {
                    // Simply proceed with the next file.
                }
            }

            $this->sendProgress(++$i, $totalItems);
        }
    }

    /**
     * Prunes removed files from the index.
     *
     * @return void
     */
    public function pruneRemovedFiles(): void
    {
        $this->storage->beginTransaction();

        foreach ($this->getFileModifiedMap() as $fileName => $file) {
            if (!file_exists($fileName)) {
                $this->storage->delete($file);
            }
        }

        $this->storage->commitTransaction();
    }

    /**
     * @return Structures\File[]
     */
    protected function getFileModifiedMap(): array
    {
        $files = $this->storage->getFiles();

        $map = [];

        foreach ($files as $file) {
            $map[$file->getPath()] = $file;
        }

        return $map;
    }
}
