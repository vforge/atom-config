<?php

namespace PhpIntegrator\Utility;

/**
 * Enumeration of special docblock types.
 */
class SpecialDocblockType
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
    public const OBJECT_   = 'object';

    /**
     * @var string
     */
    public const MIXED_    = 'mixed';

    /**
     * @var string
     */
    public const ARRAY_    = 'array';

    /**
     * @var string
     */
    public const RESOURCE_ = 'resource';

    /**
     * @var string
     */
    public const VOID_     = 'void';

    /**
     * @var string
     */
    public const NULL_     = 'null';

    /**
     * @var string
     */
    public const CALLABLE_ = 'callable';

    /**
     * @var string
     */
    public const FALSE_    = 'false';

    /**
     * @var string
     */
    public const TRUE_     = 'true';

    /**
     * @var string
     */
    public const SELF_     = 'self';

    /**
     * @var string
     */
    public const STATIC_   = 'static';

    /**
     * @var string
     */
    public const PARENT_   = 'parent';

    /**
     * @var string
     */
    public const THIS_     = '$this';

    /**
     * @var string
     */
    public const ITERABLE_ = 'iterable';
}
