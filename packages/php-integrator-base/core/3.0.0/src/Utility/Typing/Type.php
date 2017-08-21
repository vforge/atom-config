<?php

namespace PhpIntegrator\Utility\Typing;

/**
 * Represents a (parameter, property, constant) type.
 *
 * This is a value object and immutable.
 */
class Type
{
    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     */
    protected function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @param string $type
     *
     * @return static
     */
    public static function createFromString(string $type)
    {
        $isReservedKeyword = in_array($type, [
            SpecialTypeString::STRING_,
            SpecialTypeString::INT_,
            SpecialTypeString::NULL_,
            SpecialTypeString::BOOL_,
            SpecialTypeString::FLOAT_,
            SpecialTypeString::ARRAY_,
            SpecialTypeString::VOID_,
            SpecialTypeString::CALLABLE_,
            SpecialTypeString::ITERABLE_
        ], true);

        return $isReservedKeyword ? new SpecialType($type) : new ClassType($type);
    }
}
