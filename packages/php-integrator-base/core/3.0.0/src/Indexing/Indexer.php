<?php

namespace PhpIntegrator\Indexing;

use LogicException;

use Evenement\EventEmitterTrait;
use Evenement\EventEmitterInterface;

use PhpIntegrator\Utility\SourceCodeStreamReader;

/**
 * Handles indexation of PHP code.
 */
class Indexer implements EventEmitterInterface
{
    use EventEmitterTrait;

    /**
     * @var string
     */
    public const INDEXING_FAILED_EVENT = 'indexingFailed';

    /**
     * @var string
     */
    public const INDEXING_SUCCEEDED_EVENT = 'indexingSucceeded';

    /**
     * @var ProjectIndexer
     */
    private $projectIndexer;

    /**
     * @var SourceCodeStreamReader
     */
    private $sourceCodeStreamReader;

    /**
     * @var callable|null
     */
    private $progressStreamingCallback;

    /**
     * @param ProjectIndexer         $projectIndexer
     * @param SourceCodeStreamReader $sourceCodeStreamReader
     */
    public function __construct(ProjectIndexer $projectIndexer, SourceCodeStreamReader $sourceCodeStreamReader)
    {
        $this->projectIndexer = $projectIndexer;
        $this->sourceCodeStreamReader = $sourceCodeStreamReader;
    }

    /**
     * @param string[] $paths
     * @param bool     $useStdin
     * @param bool     $doStreamProgress
     * @param string[] $excludedPaths
     * @param string[] $extensionsToIndex
     *
     * @return bool Whether indexing succeeded or not.
     */
    public function reindex(
        array $paths,
        bool $useStdin,
        bool $doStreamProgress,
        array $excludedPaths = [],
        array $extensionsToIndex = ['php']
    ): bool {
        if ($doStreamProgress && !$this->getProgressStreamingCallback()) {
            throw new LogicException('No progress streaming callback configured whilst streaming was requestd!');
        }

        $this->projectIndexer
            ->setProgressStreamingCallback($doStreamProgress ? $this->getProgressStreamingCallback() : null);

        $sourceOverrideMap = [];

        if ($useStdin) {
            $sourceOverrideMap[$paths[0]] = $this->sourceCodeStreamReader->getSourceCodeFromStdin();
        }

        try {
            $this->projectIndexer->index($paths, $extensionsToIndex, $excludedPaths, $sourceOverrideMap);
        } catch (IndexingFailedException $e) {
            $this->emit(self::INDEXING_FAILED_EVENT);

            return false;
        }

        $this->emit(self::INDEXING_SUCCEEDED_EVENT);

        return true;
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
}
