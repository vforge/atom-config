<?php

namespace PhpIntegrator\Utility;

use OutOfBoundsException;

/**
 * Contains static utility functionality for dealing with source code.
 */
class SourceCodeHelpers
{
    /**
     * Calculates the 1-indexed line the specified byte offset is located at.
     *
     * @param string $source
     * @param int    $offset
     *
     * @throws OutOfBoundsException
     *
     * @return int
     */
    public static function calculateLineByOffset(string $source, int $offset): int
    {
        if (!$offset) {
            return 1;
        }

        if ($offset > strlen($source)) {
            throw new OutOfBoundsException('The offset is larger than the length of the string');
        }

        return substr_count($source, "\n", 0, $offset) + 1;
    }

    /**
     * Calculates the 0-indexed offset for the specified 0-indexed line and character/
     *
     * @param string $source
     * @param int    $line
     * @param int    $byteCharacter
     *
     * @throws OutOfBoundsException
     *
     * @return int
     */
    public static function calculateOffsetByLineCharacter(string $source, int $line, int $byteCharacter): int
    {
        $i = 0;
        $currentLine = 0;
        $offsetOnLine = 0;
        $length = strlen($source);

        while ($i < $length) {
            if ($source[$i] === "\n") {
                ++$currentLine;
                $offsetOnLine = 0;
            }

            if ($currentLine === $line && $offsetOnLine === $byteCharacter) {
                return $i;
            }

            ++$i;
            ++$offsetOnLine;
        }

        throw new OutOfBoundsException("Line {$line} and line byte offset {$byteCharacter} are not in range of string");
    }

    /**
     * Retrieves the character offset from the specified byte offset in the specified string. The result will always be
     * smaller than or equal to the passed in value, depending on the amount of multi-byte characters encountered.
     *
     * @param int    $byteOffset
     * @param string $string
     *
     * @return int
     */
    // public static function getCharacterOffsetFromByteOffset(int $byteOffset, string $string): int
    // {
    //     return mb_strlen(mb_strcut($string, 0, $byteOffset));
    // }

    /**
     * Retrieves the byte offset from the specified character offset in the specified string. The result will always be
     * larger than or equal to the passed in value, depending on the amount of multi-byte characters encountered.
     *
     * @param int    $characterOffset
     * @param string $string
     *
     * @return int
     */
    public static function getByteOffsetFromCharacterOffset(int $characterOffset, string $string): int
    {
        return strlen(mb_substr($string, 0, $characterOffset));
    }
}
