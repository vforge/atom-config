<?php

namespace PhpIntegrator\Analysis\SourceCodeReading;

/**
 * Source code reader that reads the source code for a file from the file itself.
 */
class FileSourceCodeFileReader implements FileSourceCodeReaderInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var TextEncodingConverterInterface
     */
    private $textEncodingConverter;

    /**
     * @param string                         $filePath
     * @param TextEncodingConverterInterface $textEncodingConverter
     */
    public function __construct(string $filePath, TextEncodingConverterInterface $textEncodingConverter)
    {
        $this->filePath = $filePath;
        $this->textEncodingConverter = $textEncodingConverter;
    }

    /**
     * @inheritDoc
     */
    public function read(): string
    {
        if (!file_exists($this->filePath)) {
            throw new FileSourceCodeReaderException("File {$this->filePath} does not exist");
        }

        $code = @file_get_contents($this->filePath);

        if ($code === false || $code === null) {
            throw new FileSourceCodeReaderException("File {$this->filePath} could not be read, it may be protected");
        }

        return $this->textEncodingConverter->convert($code);
    }
}
