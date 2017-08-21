<?php

namespace PhpIntegrator\Indexing\Structures;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Represents a structure or classlike.
 */
abstract class Structure
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
     * @var string
     */
    protected $fqcn;

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
     * @var string|null
     */
    protected $shortDescription;

    /**
     * @var string|null
     */
    protected $longDescription;

    /**
     * @var bool
     */
    protected $isDeprecated;

    /**
     * @var bool
     */
    protected $hasDocblock;

    /**
     * @var ArrayCollection
     */
    protected $constants;

    /**
     * @var ArrayCollection
     */
    protected $properties;

    /**
     * @var ArrayCollection
     */
    protected $methods;

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
     * @return string
     */
    public function getFqcn(): string
    {
        return $this->fqcn;
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
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @return string|null
     */
    public function getLongDescription()
    {
        return $this->longDescription;
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
     * @return ClassConstant[]
     */
    public function getConstants(): array
    {
        return $this->constants->toArray();
    }

    /**
     * @param ClassConstant $constant
     *
     * @return void
     */
    public function addConstant(ClassConstant $constant): void
    {
        $this->constants->add($constant);
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array
    {
        return $this->properties->toArray();
    }

    /**
     * @param Property $property
     *
     * @return void
     */
    public function addProperty(Property $property): void
    {
        $this->properties->add($property);
    }

    /**
     * @return Method[]
     */
    public function getMethods(): array
    {
        return $this->methods->toArray();
    }

    /**
     * @param Method $method
     *
     * @return void
     */
    public function addMethod(Method $method): void
    {
        $this->methods->add($method);
    }

    /**
     * @return string
     */
    abstract public function getTypeName(): string;
}
