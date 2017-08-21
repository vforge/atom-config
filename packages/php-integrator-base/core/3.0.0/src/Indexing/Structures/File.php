<?php

namespace PhpIntegrator\Indexing\Structures;

use DateTime;
use OutOfRangeException;

use Doctrine\Common\Collections\ArrayCollection;

use Ramsey\Uuid\Uuid;

/**
 * Represents a file.
 */
class File
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $path;

    /**
     * @var DateTime
     */
    private $indexedOn;

    /**
     * @var ArrayCollection
     */
    private $constants;

    /**
     * @var ArrayCollection
     */
    private $functions;

    /**
     * @var ArrayCollection
     */
    private $structures;

    /**
     * @var ArrayCollection
     */
    private $namespaces;

    /**
     * @var ArrayCollection
     */
    private $metaStaticMethodTypes;

    /**
     * @param string          $path
     * @param DateTime        $indexedOn
     * @param FileNamespace[] $namespaces
     */
    public function __construct(string $path, DateTime $indexedOn, array $namespaces)
    {
        $this->id = (string) Uuid::uuid4();
        $this->path = $path;
        $this->indexedOn = $indexedOn;
        $this->namespaces = new ArrayCollection($namespaces);

        $this->constants = new ArrayCollection();
        $this->functions = new ArrayCollection();
        $this->structures = new ArrayCollection();
        $this->metaStaticMethodTypes = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return DateTime
     */
    public function getIndexedOn(): DateTime
    {
        return $this->indexedOn;
    }

    /**
     * @param DateTime $indexedOn
     *
     * @return static
     */
    public function setIndexedOn(DateTime $indexedOn)
    {
        $this->indexedOn = $indexedOn;
        return $this;
    }

    /**
     * @return Constant[]
     */
    public function getConstants(): array
    {
        return array_values($this->constants->toArray());
    }

    /**
     * @param Constant $constant
     */
    public function addConstant(Constant $constant): void
    {
        $this->constants->add($constant);
    }

    /**
     * @param Constant $constant
     */
    public function removeConstant(Constant $constant): void
    {
        if (!$this->constants->contains($constant)) {
            throw new OutOfRangeException('Can not remove function from file that isn\'t even part of file');
        }

        $this->constants->removeElement($constant);
    }

    /**
     * @return Function_[]
     */
    public function getFunctions(): array
    {
        return array_values($this->functions->toArray());
    }

    /**
     * @param Function_ $function
     */
    public function addFunction(Function_ $function): void
    {
        $this->functions->add($function);
    }

    /**
     * @param Function_ $function
     */
    public function removeFunction(Function_ $function): void
    {
        if (!$this->functions->contains($function)) {
            throw new OutOfRangeException('Can not remove function from file that isn\'t even part of file');
        }

        $this->functions->removeElement($function);
    }

    /**
     * @return Structure[]
     */
    public function getStructures(): array
    {
        return array_values($this->structures->toArray());
    }

    /**
     * @param Structure $structure
     */
    public function addStructure(Structure $structure): void
    {
        $this->structures->add($structure);
    }

    /**
     * @param Structure $structure
     */
    public function removeStructure(Structure $structure): void
    {
        if (!$this->structures->contains($structure)) {
            throw new OutOfRangeException('Can not remove structure from file that isn\'t even part of file');
        }

        $this->structures->removeElement($structure);
    }

    /**
     * @return FileNamespace[]
     */
    public function getNamespaces(): array
    {
        return array_values($this->namespaces->toArray());
    }

    /**
     * @param FileNamespace $namespace
     *
     * @return void
     */
    public function addNamespace(FileNamespace $namespace): void
    {
        $this->namespaces->add($namespace);
    }

    /**
     * @param FileNamespace $namespace
     */
    public function removeNamespace(FileNamespace $namespace): void
    {
        if (!$this->namespaces->contains($namespace)) {
            throw new OutOfRangeException('Can not remove namespace from file that isn\'t even part of file');
        }

        $this->namespaces->removeElement($namespace);
    }

    /**
     * @return MetaStaticMethodType[]
     */
    public function getMetaStaticMethodTypes(): array
    {
        return array_values($this->metaStaticMethodTypes->toArray());
    }

    /**
     * @param MetaStaticMethodType $metaStaticMethodType
     *
     * @return void
     */
    public function addMetaStaticMethodType(MetaStaticMethodType $metaStaticMethodType): void
    {
        $this->metaStaticMethodTypes->add($metaStaticMethodType);
    }

    /**
     * @param MetaStaticMethodType $metaStaticMethodType
     */
    public function removeMetaStaticMethodType(MetaStaticMethodType $metaStaticMethodType): void
    {
        if (!$this->metaStaticMethodTypes->contains($metaStaticMethodType)) {
            throw new OutOfRangeException(
                'Can not remove meta static method type from file that isn\'t even part of file'
            );
        }

        $this->metaStaticMethodTypes->removeElement($metaStaticMethodType);
    }
}
