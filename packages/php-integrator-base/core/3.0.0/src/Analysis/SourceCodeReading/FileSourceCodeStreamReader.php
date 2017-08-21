<?php

namespace PhpIntegrator\Analysis\SourceCodeReading;

/**
 * Source code reader that reads the source code for a file from a stream.
 */
class FileSourceCodeStreamReader implements FileSourceCodeReaderInterface
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * @var TextEncodingConverterInterface
     */
    private $textEncodingConverter;

    /**
     * @param resource                       $stream
     * @param TextEncodingConverterInterface $textEncodingConverter
     */
    public function __construct($stream, TextEncodingConverterInterface $textEncodingConverter)
    {
        $this->stream = $stream;
        $this->textEncodingConverter = $textEncodingConverter;
    }

    /**
     * @inheritDoc
     */
    public function read(): string
    {
        $code = stream_get_contents($this->stream);

        return $this->textEncodingConverter->convert($code);
    }
}
