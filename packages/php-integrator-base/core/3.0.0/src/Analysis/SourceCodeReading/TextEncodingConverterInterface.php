<?php

namespace PhpIntegrator\Analysis\SourceCodeReading;

/**
 * Interface for classes that can convert the encoding of text.
 */
interface TextEncodingConverterInterface
{
    /**
     * @param string $text
     *
     * @return string
     */
    public function convert(string $text): string;
}
