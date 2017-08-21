<?php

namespace PhpIntegrator\Analysis\SourceCodeReading;

/**
 * Interface for classes that can provide the source code of files.
 */
interface FileSourceCodeReaderInterface
{
    /**
     * @throws FileSourceCodeReaderException
     *
     * @return string
     */
    public function read(): string;
}
