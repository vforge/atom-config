<?php

namespace PhpIntegrator\Utility;

use Closure;
use ArrayIterator;
use IteratorAggregate;

/**
 * Represents an immutable set of values.
 */
class ImmutableSet implements IteratorAggregate
{
    /**
     * @var mixed[]
     */
    private $elements;

    /**
     * @param mixed[] ...$elements
     */
    public function __construct(...$elements)
    {
        $this->elements = $elements;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * @param mixed $element
     *
     * @return bool
     */
    public function has($element): bool
    {
        return in_array($element, $this->elements, $this->isStrict());
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->toArray());
    }

    /**
     * @param ImmutableSet $other
     *
     * @return bool
     */
    public function equals(ImmutableSet $other): bool
    {
        return
            empty(array_diff($this->toArray(), $other->toArray())) &&
            empty(array_diff($other->toArray(), $this->toArray()));
    }

    /**
     * @param Closure $closure
     *
     * @return static
     */
    public function filter(Closure $closure): ImmutableSet
    {
        return new static(...array_filter($this->toArray(), $closure, true));
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * @return bool
     *
     * @api
     */
    protected function isStrict(): bool
    {
        return true;
    }
}
