<?php

namespace PhpIntegrator\Indexing\Structures;

/**
 * Base class for trait method precedences in structures.
 */
abstract class StructureTraitPrecedence
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $traitFqcn;

    /**
     * @var string
     */
    protected $name;

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
    public function getTraitFqcn(): string
    {
        return $this->traitFqcn;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
