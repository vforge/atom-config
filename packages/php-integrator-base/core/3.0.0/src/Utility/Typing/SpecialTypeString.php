<?php

namespace PhpIntegrator\Utility\Typing;

/**
 * Enumeration of string values for special types (that properties, constants, parameters, ...) can have.
 */
class SpecialTypeString
{
    /**
     * @var string
     */
    public const STRING_   = 'string';

    /**
     * @var string
     */
    public const INT_      = 'int';

    /**
     * @var string
     */
    public const BOOL_     = 'bool';

    /**
     * @var string
     */
    public const FLOAT_    = 'float';

    /**
     * @var string
     */
    public const ARRAY_    = 'array';

    /**
     * @var string
     */
    public const VOID_     = 'void';

    /**
     * @var string
     */
    public const CALLABLE_ = 'callable';

    /**
     * @var string
     */
    public const ITERABLE_ = 'iterable';

    /**
     * @var string
     */
    public const NULL_     = 'null';
}
