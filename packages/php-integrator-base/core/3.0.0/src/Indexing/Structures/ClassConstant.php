<?php

namespace PhpIntegrator\Indexing\Structures;

use Ramsey\Uuid\Uuid;

/**
 * Represents a class constant.
 */
class ClassConstant extends ConstantLike
{
    /**
     * @var Structure
     */
    private $structure;

    /**
     * @var AccessModifier
     */
    private $accessModifier;

    /**
     * @param string              $name
     * @param File                $file
     * @param int                 $startLine
     * @param int                 $endLine
     * @param string              $defaultValue
     * @param bool                $isDeprecated
     * @param bool                $hasDocblock
     * @param string|null         $shortDescription
     * @param string|null         $longDescription
     * @param string|null         $typeDescription
     * @param TypeInfo[]          $types
     * @param Structure           $structure
     * @param AccessModifier      $accessModifier
     */
    public function __construct(
        string $name,
        File $file,
        int $startLine,
        int $endLine,
        string $defaultValue,
        bool $isDeprecated,
        bool $hasDocblock,
        ?string $shortDescription,
        ?string $longDescription,
        ?string $typeDescription,
        array $types,
        Structure $structure,
        AccessModifier $accessModifier
    ) {
        $this->id = (string) Uuid::uuid4();
        $this->name = $name;
        $this->file = $file;
        $this->startLine = $startLine;
        $this->endLine = $endLine;
        $this->defaultValue = $defaultValue;
        $this->isDeprecated = $isDeprecated;
        $this->hasDocblock = $hasDocblock;
        $this->shortDescription = $shortDescription;
        $this->longDescription = $longDescription;
        $this->typeDescription = $typeDescription;
        $this->types = $types;
        $this->structure = $structure;
        $this->accessModifier = $accessModifier;

        $structure->addConstant($this);
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
}
