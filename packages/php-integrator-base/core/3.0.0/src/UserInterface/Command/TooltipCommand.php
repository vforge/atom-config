<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;

use PhpIntegrator\Indexing\StorageInterface;

use PhpIntegrator\Tooltips\TooltipResult;
use PhpIntegrator\Tooltips\TooltipProvider;

use PhpIntegrator\Utility\SourceCodeHelpers;
use PhpIntegrator\Utility\SourceCodeStreamReader;

/**
 * Command that fetches tooltip information for a specific location.
 */
class TooltipCommand extends AbstractCommand
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var TooltipProvider
     */
    private $tooltipProvider;

    /**
     * @var SourceCodeStreamReader
     */
    private $sourceCodeStreamReader;

    /**
     * @param StorageInterface       $storage
     * @param TooltipProvider        $tooltipProvider
     * @param SourceCodeStreamReader $sourceCodeStreamReader
     */
    public function __construct(
        StorageInterface $storage,
        TooltipProvider $tooltipProvider,
        SourceCodeStreamReader $sourceCodeStreamReader
    ) {
        $this->storage = $storage;
        $this->tooltipProvider = $tooltipProvider;
        $this->sourceCodeStreamReader = $sourceCodeStreamReader;
    }

    /**
     * @inheritDoc
     */
    public function execute(ArrayAccess $arguments)
    {
        if (!isset($arguments['file'])) {
            throw new InvalidArgumentsException('A --file must be supplied!');
        } elseif (!isset($arguments['offset'])) {
            throw new InvalidArgumentsException('An --offset must be supplied into the source code!');
        }

        if (isset($arguments['stdin']) && $arguments['stdin']) {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromStdin();
        } else {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromFile($arguments['file']);
        }

        $offset = $arguments['offset'];

        if (isset($arguments['charoffset']) && $arguments['charoffset'] == true) {
            $offset = SourceCodeHelpers::getByteOffsetFromCharacterOffset($offset, $code);
        }

        return $this->getTooltip($arguments['file'], $code, $offset);
    }

    /**
     * @param string $filePath
     * @param string $code
     * @param int    $offset
     *
     * @return TooltipResult|null
     */
    public function getTooltip(string $filePath, string $code, int $offset): ?TooltipResult
    {
        return $this->tooltipProvider->get($this->storage->getFileByPath($filePath), $code, $offset);
    }
}
