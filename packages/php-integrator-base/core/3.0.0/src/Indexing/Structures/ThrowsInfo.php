<?php

namespace PhpIntegrator\Indexing\Structures;

/**
 * Represents information about an exception that can be thrown by a function-like structural element.
 */
class ThrowsInfo
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $fqcn;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @param string      $type
     * @param string      $fqcn
     * @param string|null $description
     */
    public function __construct(string $type, string $fqcn, ?string $description)
    {
        $this->type = $type;
        $this->fqcn = $fqcn;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getFqcn(): string
    {
        return $this->fqcn;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }
}
