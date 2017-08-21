<?php

namespace PhpIntegrator\Indexing\Structures;

/**
 * Contains common properties for function-like structural elements.
 */
abstract class FunctionLike
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
     * @var bool
     */
    protected $isDeprecated;

    /**
     * @var string|null
     */
    protected $shortDescription;

    /**
     * @var string|null
     */
    protected $longDescription;

    /**
     * @var string|null
     */
    protected $returnDescription;

    /**
     * @var string|null
     */
    protected $returnTypeHint;

    /**
     * @var bool
     */
    protected $hasDocblock;

    /**
     * @var ThrowsInfo[]
     */
    protected $throws;

    /**
     * @var FunctionLikeParameter[]
     */
    protected $parameters;

    /**
     * @var TypeInfo[]
     */
    protected $returnTypes;

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
     * @return bool
     */
    public function getIsDeprecated(): bool
    {
        return $this->isDeprecated;
    }

    /**
     * @return string|null
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @return string|null
     */
    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    /**
     * @return string|null
     */
    public function getReturnDescription(): ?string
    {
        return $this->returnDescription;
    }

    /**
     * @return string|null
     */
    public function getReturnTypeHint(): ?string
    {
        return $this->returnTypeHint;
    }

    /**
     * @return bool
     */
    public function getHasDocblock(): bool
    {
        return $this->hasDocblock;
    }

    /**
     * @return ThrowsInfo[]
     */
    public function getThrows(): array
    {
        return $this->throws;
    }

    /**
     * @return FunctionLikeParameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters->toArray();
    }

    /**
     * @param FunctionLikeParameter $parameter
     *
     * @return void
     */
    public function addParameter(FunctionLikeParameter $parameter): void
    {
        $this->parameters->add($parameter);
    }

    /**
     * @return TypeInfo[]
     */
    public function getReturnTypes(): array
    {
        return $this->returnTypes;
    }
}
