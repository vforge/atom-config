<?php

namespace PhpIntegrator\Analysis\SourceCodeReading;

/**
 * Factory that creates instances of {@see FileSourceCodeStreamReader}.
 */
class FileSourceCodeStreamReaderFactory
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
     * @param resource $stream
     *
     * @return FileSourceCodeStreamReader
     */
    public function create($stream): FileSourceCodeStreamReader
    {
        return new FileSourceCodeStreamReader($stream, $this->textEncodingConverter);
    }
}
