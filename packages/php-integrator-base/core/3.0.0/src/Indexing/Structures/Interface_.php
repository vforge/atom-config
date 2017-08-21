<?php

namespace PhpIntegrator\Indexing\Structures;

use Doctrine\Common\Collections\ArrayCollection;

use Ramsey\Uuid\Uuid;

/**
 * Represents an interface.
 */
class Interface_ extends Structure
{
    /**
     * @var string[]
     */
    private $parentFqcns;

    /**
     * @var string[]
     */
    private $childFqcns;

    /**
     * @var string[]
     */
    private $implementorFqcns;

    /**
     * @var bool
     */
    private $isAddingImplementor = false;

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

        $this->parentFqcns = [];
        $this->childFqcns = [];
        $this->implementorFqcns = [];

        $this->constants = new ArrayCollection();
        $this->properties = new ArrayCollection();
        $this->methods = new ArrayCollection();

        $file->addStructure($this);
    }

    /**
     * @return string[]
     */
    public function getParentFqcns(): array
    {
        return $this->parentFqcns;
    }

    /**
     * @param string $fqcn
     *
     * @return void
     */
    public function addParentFqcn(string $fqcn): void
    {
        $this->parentFqcns[] = $fqcn;
    }

    /**
     * @param Interface_ $interface
     *
     * @return void
     */
    public function addParent(Interface_ $interface): void
    {
        $this->addParentFqcn($interface->getFqcn());

        $interface->childFqcns[] = $this->getFqcn();
    }

    /**
     * @return string[]
     */
    public function getChildFqcns(): array
    {
        return $this->childFqcns;
    }

    /**
     * @param Interface_ $interface
     *
     * @return void
     */
    public function addChild(Interface_ $interface): void
    {
        $interface->addParent($this);
    }

    /**
     * @return string[]
     */
    public function getImplementorFqcns(): array
    {
        return $this->implementorFqcns;
    }

    /**
     * @param Class_ $class
     *
     * @return void
     */
    public function addImplementor(Class_ $class): void
    {
        if ($this->isAddingImplementor) {
            return; // Don't loop infinitely whilst maintaining bidirectional association.
        }

        $this->isAddingImplementor = true;

        $this->implementorFqcns[] = $class->getFqcn();

        $class->addInterface($this);

        $this->isAddingImplementor = false;
    }

    /**
     * @inheritDoc
     */
    public function getTypeName(): string
    {
        return StructureTypeNameValue::INTERFACE_;
    }
}
