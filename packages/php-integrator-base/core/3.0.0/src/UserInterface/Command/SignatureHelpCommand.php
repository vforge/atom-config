<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;

use PhpIntegrator\Indexing\StorageInterface;

use PhpIntegrator\SignatureHelp\SignatureHelp;
use PhpIntegrator\SignatureHelp\SignatureHelpRetriever;

use PhpIntegrator\Utility\SourceCodeHelpers;
use PhpIntegrator\Utility\SourceCodeStreamReader;

/**
 * Allows fetching signature help (call tips) for a method or function call.
 */
class SignatureHelpCommand extends AbstractCommand
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var SignatureHelpRetriever
     */
    private $signatureHelpRetriever;

    /**
     * @var SourceCodeStreamReader
     */
    private $sourceCodeStreamReader;

    /**
     * @param StorageInterface       $storage
     * @param SignatureHelpRetriever $signatureHelpRetriever
     * @param SourceCodeStreamReader $sourceCodeStreamReader
     */
    public function __construct(
        StorageInterface $storage,
        SignatureHelpRetriever $signatureHelpRetriever,
        SourceCodeStreamReader $sourceCodeStreamReader
    ) {
        $this->storage = $storage;
        $this->signatureHelpRetriever = $signatureHelpRetriever;
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

        return $this->signatureHelp($arguments['file'], $code, $offset);
    }

    /**
     * @param string $filePath
     * @param string $code
     * @param int    $offset
     *
     * @return SignatureHelp
     */
    public function signatureHelp(string $filePath, string $code, int $offset): SignatureHelp
    {
        return $this->signatureHelpRetriever->get($this->storage->getFileByPath($filePath), $code, $offset);
    }
}
