<?php

namespace PhpIntegrator\Indexing\Structures;

use Ramsey\Uuid\Uuid;

/**
 * Represents trait method precedence in a class.
 */
class ClassTraitPrecedence extends StructureTraitPrecedence
{
    /**
     * @var Class_
     */
    private $class;

    /**
     * @param Class_ $class
     * @param string $traitFqcn
     * @param string $name
     */
    public function __construct(Class_ $class, string $traitFqcn, string $name)
    {
        $this->id = (string) Uuid::uuid4();
        $this->class = $class;
        $this->traitFqcn = $traitFqcn;
        $this->name = $name;

        $class->addTraitPrecedence($this);
    }

    /**
     * @return Class_
     */
    public function getClass(): Class_
    {
        return $this->class;
    }
}
