<?php

namespace PhpIntegrator\Analysis\SourceCodeReading;

/**
 * Converts text to UTF-8.
 */
class TextToUtf8Converter implements TextEncodingConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convert(string $code): string
    {
        $encoding = mb_detect_encoding($code, null, true);

        if (!$encoding) {
            $encoding = 'ASCII';
        }

        if (!in_array($encoding, ['UTF-8', 'ASCII'], true)) {
            $code = mb_convert_encoding($code, 'UTF-8', $encoding);
        }

        return $code;
    }
}
