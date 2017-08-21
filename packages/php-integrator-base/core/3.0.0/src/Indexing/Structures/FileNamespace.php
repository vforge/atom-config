<?php

namespace PhpIntegrator\Indexing\Structures;

use Doctrine\Common\Collections\ArrayCollection;

use Ramsey\Uuid\Uuid;

/**
 * Represents a namespace in a file.
 */
class FileNamespace
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $startLine;

    /**
     * @var int
     */
    private $endLine;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var File
     */
    private $file;

    /**
     * @var ArrayCollection
     */
    private $imports;

    /**
     * @param int                   $startLine
     * @param int                   $endLine
     * @param string|null           $name
     * @param File                  $file
     * @param FileNamespaceImport[] $imports
     */
    public function __construct(
        int $startLine,
        int $endLine,
        string $name = null,
        File $file,
        array $imports
    ) {
        $this->id = (string) Uuid::uuid4();
        $this->startLine = $startLine;
        $this->endLine = $endLine;
        $this->name = $name;
        $this->file = $file;
        $this->imports = new ArrayCollection($imports);

        $file->addNamespace($this);
    }

    /**
     * @return int
     */
    public function getId(): string
    {
        return $this->id;
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
     * @return string|null
     */
    public function getName(): ?string
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
     * @return FileNamespaceImport[]
     */
    public function getImports(): array
    {
        return $this->imports->toArray();
    }

    /**
     * @param FileNamespaceImport $import
     *
     * @return void
     */
    public function addImport(FileNamespaceImport $import): void
    {
        $this->imports->add($import);
    }
}
