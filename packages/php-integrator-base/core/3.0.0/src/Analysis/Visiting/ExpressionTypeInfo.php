<?php

namespace PhpIntegrator\Analysis\Visiting;

use PhpParser\Node;

/**
 * Holds information about an expression's type.
 */
class ExpressionTypeInfo
{
    /**
     * The node that best describes the item.
     *
     * This is usually a node such as an assignment expression or a method parameter that the item is involved in. For
     * example, if a node "$a = $b" is encountered, followed by a node "$a = $c", then the best match for the type of
     * $a would be the node that describes "$a = $c".
     *
     * @var Node|null
     */
    private $bestMatch;

    /**
     * Describes an override of the type.
     *
     * Type overrides are usually present in inline docblocks that override the type. This field would be filled with
     * that type (override) that is set for this item. For example, "/** @var Foo $a * /" describes the type of $a to
     * be overridden to be "Foo".
     *
     * @var string|null
     */
    private $bestTypeOverrideMatch;

    /**
     * The line to type override was encountered at.
     *
     * @var int|null
     */
    private $bestTypeOverrideMatchLine;

    /**
     * @var TypePossibilityMap
     */
    private $typePossibilityMap;

    /**
     *
     */
    public function __construct()
    {
        $this->typePossibilityMap = new TypePossibilityMap();
    }

    /**
     * @return Node|null
     */
    public function getBestMatch(): ?Node
    {
        return $this->bestMatch;
    }

    /**
     * @param Node|null $bestMatch
     *
     * @return static
     */
    public function setBestMatch(?Node $bestMatch)
    {
        $this->bestMatch = $bestMatch;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBestTypeOverrideMatch(): ?string
    {
        return $this->bestTypeOverrideMatch;
    }

    /**
     * @param string|null $bestTypeOverrideMatch
     *
     * @return static
     */
    public function setBestTypeOverrideMatch(?string $bestTypeOverrideMatch)
    {
        $this->bestTypeOverrideMatch = $bestTypeOverrideMatch;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getBestTypeOverrideMatchLine(): ?int
    {
        return $this->bestTypeOverrideMatchLine;
    }

    /**
     * @param int|null $bestTypeOverrideMatchLine
     *
     * @return static
     */
    public function setBestTypeOverrideMatchLine(?int $bestTypeOverrideMatchLine)
    {
        $this->bestTypeOverrideMatchLine = $bestTypeOverrideMatchLine;
        return $this;
    }

    /**
     * @return TypePossibilityMap
     */
    public function getTypePossibilityMap(): TypePossibilityMap
    {
        return $this->typePossibilityMap;
    }

    /**
     * @param TypePossibilityMap $typePossibilityMap
     *
     * @return static
     */
    public function setTypePossibilityMap(TypePossibilityMap $typePossibilityMap)
    {
        $this->typePossibilityMap = $typePossibilityMap;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasBestMatch(): bool
    {
        return ($this->getBestMatch() !== null);
    }

    /**
     * @return bool
     */
    public function hasBestTypeOverrideMatch(): bool
    {
        return ($this->getBestTypeOverrideMatch() !== null);
    }
}
