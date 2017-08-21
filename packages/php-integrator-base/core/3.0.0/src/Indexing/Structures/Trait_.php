<?php

namespace PhpIntegrator\Indexing\Structures;

use DomainException;

use Doctrine\Common\Collections\ArrayCollection;

use Ramsey\Uuid\Uuid;

/**
 * Represents a trait.
 */
class Trait_ extends Structure
{
    /**
     * @var string[]
     */
    private $traitFqcns;

    /**
     * @var string[]
     */
    private $traitUserFqcns;

    /**
     * @var ArrayCollection
     */
    private $traitAliases;

    /**
     * @var ArrayCollection
     */
    private $traitPrecedences;

    /**
     * @var bool
     */
    private $isAddingTrait = false;

    /**
     * @var bool
     */
    private $isAddingTraitUser = false;

    /**
     * @param string        $name
     * @param string        $fqcn
     * @param File          $file
     * @param int           $startLine
     * @param int           $endLine
     * @param string|null   $shortDescription
     * @param string|null   $longDescription
     * @param bool          $isDeprecated
     * @param bool          $hasDocblock
     */
    public function __construct(
        string $name,
        string $fqcn,
        File $file,
        int $startLine,
        int $endLine,
        ?string $shortDescription,
        ?string $longDescription,
        bool $isDeprecated,
        bool $hasDocblock
    ) {
        $this->id = (string) Uuid::uuid4();
        $this->name = $name;
        $this->fqcn = $fqcn;
        $this->file = $file;
        $this->startLine = $startLine;
        $this->endLine = $endLine;
        $this->shortDescription = $shortDescription;
        $this->longDescription = $longDescription;
        $this->isDeprecated = $isDeprecated;
        $this->hasDocblock = $hasDocblock;

        $this->traitFqcns = [];
        $this->traitUserFqcns = [];

        $this->traitAliases = new ArrayCollection();
        $this->traitPrecedences = new ArrayCollection();

        $this->constants = new ArrayCollection();
        $this->properties = new ArrayCollection();
        $this->methods = new ArrayCollection();

        $file->addStructure($this);
    }

    /**
     * @return string[]
     */
    public function getTraitFqcns(): array
    {
        return $this->traitFqcns;
    }

    /**
     * @param string $fqcn
     *
     * @return void
     */
    public function addTraitFqcn(string $fqcn): void
    {
        $this->traitFqcns[] = $fqcn;
    }

    /**
     * @param Trait_ $trait
     *
     * @return void
     */
    public function addTrait(Trait_ $trait): void
    {
        if ($this->isAddingTrait) {
            return; // Don't loop infinitely whilst maintaining bidirectional association.
        }

        $this->isAddingTrait = true;

        $this->addTraitFqcn($trait->getFqcn());

        $trait->addTraitUser($this);

        $this->isAddingTrait = false;
    }

    /**
     * @return string[]
     */
    public function getTraitUserFqcns(): array
    {
        return $this->traitUserFqcns;
    }

    /**
     * @param Class_|Trait_ $structure
     *
     * @return void
     */
    public function addTraitUser(Structure $structure): void
    {
        if (!$structure instanceof Class_ && !$structure instanceof Trait_) {
            throw new DomainException('Only classes and other traits can use traits');
        }

        if ($this->isAddingTraitUser) {
            return; // Don't loop infinitely whilst maintaining bidirectional association.
        }

        $this->isAddingTraitUser = true;

        $this->traitUserFqcns[] = $structure->getFqcn();

        $structure->addTrait($this);

        $this->isAddingTraitUser = false;
    }

    /**
     * @return TraitTraitAlias[]
     */
    public function getTraitAliases(): array
    {
        return $this->traitAliases->toArray();
    }

    /**
     * @param TraitTraitAlias $structureTraitAlias
     *
     * @return void
     */
    public function addTraitAlias(TraitTraitAlias $structureTraitAlias): void
    {
        $this->traitAliases->add($structureTraitAlias);
    }

    /**
     * @return TraitTraitPrecedence[]
     */
    public function getTraitPrecedences(): array
    {
        return $this->traitPrecedences->toArray();
    }

    /**
     * @param TraitTraitPrecedence $structureTraitPrecedence
     *
     * @return void
     */
    public function addTraitPrecedence(TraitTraitPrecedence $structureTraitPrecedence): void
    {
        $this->traitPrecedences->add($structureTraitPrecedence);
    }

    /**
     * @inheritDoc
     */
    public function getTypeName(): string
    {
        return StructureTypeNameValue::TRAIT_;
    }
}
