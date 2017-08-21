<?php

namespace PhpIntegrator\Tooltips;

use JsonSerializable;

use PhpIntegrator\Common\Range;

/**
 * Represents the result of a tooltip provision.
 *
 * This is a value object and immutable.
 *
 * @see https://github.com/Microsoft/language-server-protocol/blob/master/versions/protocol-2-x.md#hover-request
 */
class TooltipResult implements JsonSerializable
{
    /**
     * The contents of the tooltip.
     *
     * Specified in markdown format.
     *
     * @var string
     */
    private $contents;

    /**
     * The range that the tooltip applies to, if any.
     *
     * @var Range|null
     */
    private $range;

    /**
     * @param string     $contents
     * @param Range|null $range
     */
    public function __construct(string $contents, Range $range = null)
    {
        $this->contents = $contents;
        $this->range = $range;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return $this->contents;
    }

    /**
     * @return Range|null
     */
    public function getRange(): ?Range
    {
        return $this->range;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'contents' => $this->getContents(),
            'range'    => $this->getRange()
        ];
    }
}
