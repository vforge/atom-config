<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;

use PhpIntegrator\Indexing\StorageInterface;

use PhpIntegrator\Linting\Linter;
use PhpIntegrator\Linting\LintingSettings;

use PhpIntegrator\Utility\SourceCodeStreamReader;

/**
 * Command that lints a file for various problems.
 */
class LintCommand extends AbstractCommand
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var SourceCodeStreamReader
     */
    private $sourceCodeStreamReader;

    /**
     * @var Linter
     */
    private $linter;

    /**
     * @param StorageInterface       $storage
     * @param SourceCodeStreamReader $sourceCodeStreamReader
     * @param Linter                 $linter
     */
    public function __construct(
        StorageInterface $storage,
        SourceCodeStreamReader $sourceCodeStreamReader,
        Linter $linter
    ) {
        $this->storage = $storage;
        $this->sourceCodeStreamReader = $sourceCodeStreamReader;
        $this->linter = $linter;
    }

    /**
     * @inheritDoc
     */
    public function execute(ArrayAccess $arguments)
    {
        if (!isset($arguments['file'])) {
            throw new InvalidArgumentsException('A file name is required for this command.');
        }

        $code = null;

        if (isset($arguments['stdin']) && $arguments['stdin']) {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromStdin();
        } else {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromFile($arguments['file']);
        }

        $settings = new LintingSettings(
            !isset($arguments['no-unknown-classes']) || !$arguments['no-unknown-classes'],
            !isset($arguments['no-unknown-members']) || !$arguments['no-unknown-members'],
            !isset($arguments['no-unknown-global-functions']) || !$arguments['no-unknown-global-functions'],
            !isset($arguments['no-unknown-global-constants']) || !$arguments['no-unknown-global-constants'],
            !isset($arguments['no-docblock-correctness']) || !$arguments['no-docblock-correctness'],
            !isset($arguments['no-unused-use-statements']) || !$arguments['no-unused-use-statements'],
            !isset($arguments['no-missing-documentation']) || !$arguments['no-missing-documentation']
        );

        return $this->lint($arguments['file'], $code, $settings);
    }

    /**
     * @param string          $filePath
     * @param string          $code
     * @param LintingSettings $settings
     *
     * @return array
     */
    public function lint(string $filePath, string $code, LintingSettings $settings): array
    {
        return $this->linter->lint($this->storage->getFileByPath($filePath), $code, $settings);
    }
}
