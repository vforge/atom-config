<?php

namespace PhpIntegrator\Analysis\SourceCodeReading;

/**
 * Factory that creates instances of {@see FileSourceCodeFileReader}.
 */
class FileSourceCodeFileReaderFactory
{
    /**
     * @var TextEncodingConverterInterface
     */
    private $textEncodingConverter;

    /**
     * @param TextEncodingConverterInterface $textEncodingConverter
     */
    public function __construct(TextEncodingConverterInterface $textEncodingConverter)
    {
        $this->textEncodingConverter = $textEncodingConverter;
    }

    /**
     * @param string $filePath
     *
     * @return FileSourceCodeFileReader
     */
    public function create(string $filePath): FileSourceCodeFileReader
    {
        return new FileSourceCodeFileReader($filePath, $this->textEncodingConverter);
    }
}
