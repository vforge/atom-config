<?php

namespace PhpIntegrator\Utility;

use JsonSerializable;

/**
 * Represents data for a namespace.
 *
 * This is a value object and immutable.
 */
class NamespaceData implements JsonSerializable
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var int
     */
    private $startLine;

    /**
     * @var int
     */
    private $endLine;

    /**
     * @param string|null $name
     * @param int         $startLine
     * @param int         $endLine
     */
    public function __construct(?string $name, int $startLine, int $endLine)
    {
        $this->name = $name;
        $this->startLine = $startLine;
        $this->endLine = $endLine;
    }

    /**
     * @param int $line
     *
     * @return bool
     */
    public function containsLine(int $line): bool
    {
        return (
            $line >= $this->getStartLine() &&
            ($line <= $this->getEndLine() || $this->getEndLine() === null)
        );
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getStartLine(): int
    {
        return $this->startLine;
    }

    /**
     * @return int
     */
    public function getEndLine(): ?int
    {
        return $this->endLine;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'name'      => $this->getName(),
            'startLine' => $this->getStartLine(),
            'endLine'   => $this->getEndLine()
        ];
    }
}
