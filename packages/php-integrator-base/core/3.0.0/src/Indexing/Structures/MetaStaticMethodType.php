<?php

namespace PhpIntegrator\Indexing\Structures;

use Ramsey\Uuid\Uuid;

/**
 * Represents metadata related to static method types.
 */
class MetaStaticMethodType
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var File
     */
    private $file;

    /**
     * @var string
     */
    private $fqcn;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $argumentIndex;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $valueNodeType;

    /**
     * @var string
     */
    private $returnType;

    /**
     * @param File   $file
     * @param string $fqcn
     * @param string $name
     * @param int    $argumentIndex
     * @param string $value
     * @param string $valueNodeType
     * @param string $returnType
     */
    public function __construct(
        File $file,
        string $fqcn,
        string $name,
        int $argumentIndex,
        string $value,
        string $valueNodeType,
        string $returnType
    ) {
        $this->id = (string) Uuid::uuid4();
        $this->file = $file;
        $this->fqcn = $fqcn;
        $this->name = $name;
        $this->argumentIndex = $argumentIndex;
        $this->value = $value;
        $this->valueNodeType = $valueNodeType;
        $this->returnType = $returnType;

        $file->addMetaStaticMethodType($this);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getFqcn(): string
    {
        return $this->fqcn;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getArgumentIndex(): int
    {
        return $this->argumentIndex;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getValueNodeType(): string
    {
        return $this->valueNodeType;
    }

    /**
     * @return string
     */
    public function getReturnType(): string
    {
        return $this->returnType;
    }
}
