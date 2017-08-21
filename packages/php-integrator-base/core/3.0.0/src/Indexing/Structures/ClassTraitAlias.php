<?php

namespace PhpIntegrator\Indexing\Structures;

use Ramsey\Uuid\Uuid;

/**
 * Represents an aliased trait method in a class.
 */
class ClassTraitAlias extends StructureTraitAlias
{
    /**
     * @var Class_
     */
    private $class;

    /**
     * @param Class_           $class
     * @param string|null         $traitFqcn
     * @param AccessModifier|null $accessModifier
     * @param string              $name
     * @param string|null         $alias
     */
    public function __construct(
        Class_ $class,
        ?string $traitFqcn,
        ?AccessModifier $accessModifier,
        string $name,
        ?string $alias
    ) {
        $this->id = (string) Uuid::uuid4();
        $this->class = $class;
        $this->traitFqcn = $traitFqcn;
        $this->accessModifier = $accessModifier;
        $this->name = $name;
        $this->alias = $alias;

        $class->addTraitAlias($this);
    }

    /**
     * @return Class_
     */
    public function getClass(): Class_
    {
        return $this->class;
    }
}
