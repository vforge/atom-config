<?php

namespace PhpIntegrator\Indexing\Structures;

use Ramsey\Uuid\Uuid;

/**
 * Represents a (global) constant.
 */
class Constant extends ConstantLike
{
    /**
     * @var string
     */
    protected $fqcn;

    /**
     * @param string              $name
     * @param string              $fqcn
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
     */
    public function __construct(
        string $name,
        string $fqcn,
        File $file,
        int $startLine,
        int $endLine,
        string $defaultValue,
        bool $isDeprecated,
        bool $hasDocblock,
        ?string $shortDescription,
        ?string $longDescription,
        ?string $typeDescription,
        array $types
    ) {
        $this->id = (string) Uuid::uuid4();
        $this->name = $name;
        $this->fqcn = $fqcn;
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

        $file->addConstant($this);
    }

    /**
     * @return string
     */
    public function getFqcn(): string
    {
        return $this->fqcn;
    }
}
