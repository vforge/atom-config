<?php

namespace PhpIntegrator\Indexing\Structures;

use Ramsey\Uuid\Uuid;

/**
 * Represents an import in a namespace inside a file.
 */
class FileNamespaceImport
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $kind;

    /**
     * @var FileNamespace
     */
    private $namespace;

    /**
     * @param int           $line
     * @param string        $alias
     * @param string        $name
     * @param string        $kind
     * @param FileNamespace $namespace
     */
    public function __construct(int $line, string $alias, string $name, string $kind, FileNamespace $namespace)
    {
        $this->id = (string) Uuid::uuid4();
        $this->line = $line;
        $this->alias = $alias;
        $this->name = $name;
        $this->kind = $kind;
        $this->namespace = $namespace;

        $namespace->addImport($this);
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
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * @return FileNamespace
     */
    public function getNamespace(): FileNamespace
    {
        return $this->namespace;
    }
}
