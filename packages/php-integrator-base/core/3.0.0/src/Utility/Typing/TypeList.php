<?php

namespace PhpIntegrator\Utility\Typing;

use PhpIntegrator\Utility\ImmutableSet;

/**
 * Represents a list of (parameter, property, constant) types.
 *
 * This is a value object and immutable.
 */
final class TypeList extends ImmutableSet
{
    /**
     * @param Type[] ...$elements
     */
    public function __construct(Type ...$elements)
    {
        parent::__construct(...$elements);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function hasStringType(string $type): bool
    {
        return $this->has(Type::createFromString($type));
    }

    /**
     * @param string[] ...$types
     *
     * @return self
     */
    public static function createFromStringTypeList(string ...$types): self
    {
        return new self(...array_map(function (string $type) {
            return Type::createFromString($type);
        }, $types));
    }

    /**
     * @inheritDoc
     */
    protected function isStrict(): bool
    {
        return false;
    }
}
