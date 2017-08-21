<?php

namespace PhpIntegrator\Indexing\Structures;


/**
 * Base class for aliased trait methods in a structure.
 */
abstract class StructureTraitAlias
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $traitFqcn;

    /**
     * @var AccessModifier|null
     */
    protected $accessModifier;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $alias;


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTraitFqcn(): ?string
    {
        return $this->traitFqcn;
    }

    /**
     * @return AccessModifier|null
     */
    public function getAccessModifier(): ?AccessModifier
    {
        return $this->accessModifier;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }
}
