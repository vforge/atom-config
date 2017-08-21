<?php

namespace PhpIntegrator\Indexing\Structures;

use Ramsey\Uuid\Uuid;

/**
 * Represents a property.
 */
class Property
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var File
     */
    private $file;

    /**
     * @var int
     */
    private $startLine;

    /**
     * @var int
     */
    private $endLine;

    /**
     * @var string|null
     */
    private $defaultValue;

    /**
     * @var bool
     */
    private $isDeprecated;

    /**
     * @var bool
     */
    private $isMagic;

    /**
     * @var bool
     */
    private $isStatic;

    /**
     * @var bool
     */
    private $hasDocblock;

    /**
     * @var string|null
     */
    private $shortDescription;

    /**
     * @var string|null
     */
    private $longDescription;

    /**
     * @var string|null
     */
    private $typeDescription;

    /**
     * @var Structure
     */
    private $structure;

    /**
     * @var AccessModifier
     */
    private $accessModifier;

    /**
     * @var TypeInfo[]
     */
    private $types;

    /**
     * @param string         $name
     * @param File           $file
     * @param int            $startLine
     * @param int            $endLine
     * @param string|null    $defaultValue
     * @param bool           $isDeprecated
     * @param bool           $isMagic
     * @param bool           $isStatic
     * @param bool           $hasDocblock
     * @param string|null    $shortDescription
     * @param string|null    $longDescription
     * @param string|null    $typeDescription
     * @param Structure      $structure
     * @param AccessModifier $accessModifier
     * @param TypeInfo[]     $types
     */
    public function __construct(
        string $name,
        File $file,
        int $startLine,
        int $endLine,
        ?string $defaultValue,
        bool $isDeprecated,
        bool $isMagic,
        bool $isStatic,
        bool $hasDocblock,
        ?string $shortDescription,
        ?string $longDescription,
        ?string $typeDescription,
        Structure $structure,
        AccessModifier $accessModifier,
        array $types
    ) {
        $this->id = (string) Uuid::uuid4();
        $this->name = $name;
        $this->file = $file;
        $this->startLine = $startLine;
        $this->endLine = $endLine;
        $this->defaultValue = $defaultValue;
        $this->isDeprecated = $isDeprecated;
        $this->isMagic = $isMagic;
        $this->isStatic = $isStatic;
        $this->hasDocblock = $hasDocblock;
        $this->shortDescription = $shortDescription;
        $this->longDescription = $longDescription;
        $this->typeDescription = $typeDescription;
        $this->structure = $structure;
        $this->accessModifier = $accessModifier;
        $this->types = $types;

        $structure->addProperty($this);
    }

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
     * @return string|null
     */
    public function getDefaultValue(): ?string
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
    public function getIsMagic(): bool
    {
        return $this->isMagic;
    }

    /**
     * @return bool
     */
    public function getIsStatic(): bool
    {
        return $this->isStatic;
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
     * @return Structure
     */
    public function getStructure(): Structure
    {
        return $this->structure;
    }

    /**
     * @return AccessModifier
     */
    public function getAccessModifier(): AccessModifier
    {
        return $this->accessModifier;
    }

    /**
     * @return TypeInfo[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}
