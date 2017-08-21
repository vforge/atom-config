<?php

namespace PhpIntegrator\Indexing\Structures;

use Doctrine\Common\Collections\ArrayCollection;

use Ramsey\Uuid\Uuid;

/**
 * Represents a (global) function.
 */
class Function_ extends FunctionLike
{
    /**
     * @var string
     */
    protected $fqcn;

    /**
     * @param string              $name
     * @param string              $fqcn
     * @param File                $file
     * @param int                 $startLine
     * @param int                 $endLine
     * @param bool                $isDeprecated
     * @param string|null         $shortDescription
     * @param string|null         $longDescription
     * @param string|null         $returnDescription
     * @param string|null         $returnTypeHint
     * @param bool                $hasDocblock
     * @param array[]             $throws
     * @param TypeInfo[]          $returnTypes
     */
    public function __construct(
        string $name,
        string $fqcn,
        File $file,
        int $startLine,
        int $endLine,
        bool $isDeprecated,
        ?string $shortDescription,
        ?string $longDescription,
        ?string $returnDescription,
        ?string $returnTypeHint,
        bool $hasDocblock,
        array $throws,
        array $returnTypes
    ) {
        $this->id = (string) Uuid::uuid4();
        $this->name = $name;
        $this->fqcn = $fqcn;
        $this->file = $file;
        $this->startLine = $startLine;
        $this->endLine = $endLine;
        $this->isDeprecated = $isDeprecated;
        $this->shortDescription = $shortDescription;
        $this->longDescription = $longDescription;
        $this->returnDescription = $returnDescription;
        $this->returnTypeHint = $returnTypeHint;
        $this->hasDocblock = $hasDocblock;
        $this->throws = $throws;
        $this->returnTypes = $returnTypes;

        $this->parameters = new ArrayCollection();

        $file->addFunction($this);
    }

    /**
     * @return string
     */
    public function getFqcn(): string
    {
        return $this->fqcn;
    }
}
