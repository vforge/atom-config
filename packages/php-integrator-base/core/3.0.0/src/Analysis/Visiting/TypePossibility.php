<?php

namespace PhpIntegrator\Analysis\Visiting;

use DomainException;

/**
 * Describes a type's possibility.
 */
class TypePossibility
{
    /**
     * Indicates a type is guaranteed.
     *
     * In cases such as:
     *
     *     if ($a instanceof A) {
     *         if ($a instanceof B) {
     *             // Examined position
     *         }
     *     }
     *
     * ... the type is guaranteed to be A and B as you can say with absolute 100% certainty that $a is of both types.
     *
     * @example In "if ($a === null)", $a is guaranteed to be "null".
     * @example In "if (!$a)", $a is guaranteed to be either "null", "int" (with value 0), "string" (empty value), ...
     *
     * @var int
     */
    public const TYPE_GUARANTEED = 1;

    /**
     * Indicates that a type is impossible.
     *
     * Note the distinction between "if ($a !== null)" and "if (!$a)". The former can say for sure that "null" is not
     * the type of "$a", but the latter can only state that $a is truthy, whilst it can still be an int (with value 0),
     * float (with value 0.0), an empty string, ...
     *
     * @example In "if ($a !== null)", the type of $a could never possibly be "null".
     *
     * @var int
     */
    public const TYPE_IMPOSSIBLE = 2;

    /**
     * @param int $possibility
     *
     * @return int
     */
    public static function getReverse(int $possibility): int
    {
        if ($possibility === self::TYPE_GUARANTEED) {
            return self::TYPE_IMPOSSIBLE;
        } elseif ($possibility === self::TYPE_IMPOSSIBLE) {
            return self::TYPE_GUARANTEED;
        }

        throw new DomainException('Unknown type possibility specified');
    }
}
