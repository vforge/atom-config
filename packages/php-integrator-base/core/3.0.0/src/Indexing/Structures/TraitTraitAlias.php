<?php

namespace PhpIntegrator\Indexing\Structures;

use Ramsey\Uuid\Uuid;

/**
 * Represents an aliased trait method in a trait.
 */
class TraitTraitAlias extends StructureTraitAlias
{
    /**
     * @var Trait_
     */
    private $trait;

    /**
     * @param Trait_              $trait
     * @param string|null         $traitFqcn
     * @param AccessModifier|null $accessModifier
     * @param string              $name
     * @param string|null         $alias
     */
    public function __construct(
        Trait_ $trait,
        ?string $traitFqcn,
        ?AccessModifier $accessModifier,
        string $name,
        ?string $alias
    ) {
        $this->id = (string) Uuid::uuid4();
        $this->trait = $trait;
        $this->traitFqcn = $traitFqcn;
        $this->accessModifier = $accessModifier;
        $this->name = $name;
        $this->alias = $alias;

        $trait->addTraitAlias($this);
    }

    /**
     * @return Trait_
     */
    public function getTrait(): Trait_
    {
        return $this->trait;
    }
}
