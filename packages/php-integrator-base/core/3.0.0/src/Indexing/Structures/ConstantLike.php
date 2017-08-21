<?php

namespace PhpIntegrator\Indexing\Structures;

/**
 * Contains common properties for constant-like structural elements.
 */
abstract class ConstantLike
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var int
     */
    protected $startLine;

    /**
     * @var int
     */
    protected $endLine;

    /**
     * @var string
     */
    protected $defaultValue;

    /**
     * @var bool
     */
    protected $isDeprecated;

    /**
     * @var bool
     */
    protected $hasDocblock;

    /**
     * @var string|null
     */
    protected $shortDescription;

    /**
     * @var string|null
     */
    protected $longDescription;

    /**
     * @var string|null
     */
    protected $typeDescription;

    /**
     * @var TypeInfo[]
     */
    protected $types;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
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
    public function getEndLine(): int
    {
        return $this->endLine;
    }

    /**
     * @return string
     */
    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    /**
     * @return bool
     */
    public function getIsDeprecated(): bool
    {
        return $this->isDeprecated;
    }

    /**
     * @return bool
     */
    public function getHasDocblock(): bool
    {
        return $this->hasDocblock;
    }

    /**
     * @return string|null
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @return string|null
     */
    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    /**
     * @return string|null
     */
    public function getTypeDescription(): ?string
    {
        return $this->typeDescription;
    }

    /**
     * @return TypeInfo[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}
