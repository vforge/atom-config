<?php

namespace PhpIntegrator\Indexing\Structures;

use Ramsey\Uuid\Uuid;

/**
 * Represents a function parameter.
 */
class FunctionParameter extends FunctionLikeParameter
{
    /**
     * @var Function_
     */
    private $function;

    /**
     * @param Function_   $function
     * @param string      $name
     * @param string|null $typeHint
     * @param TypeInfo[]  $types
     * @param string|null $description
     * @param string|null $defaultValue
     * @param bool        $isReference
     * @param bool        $isOptional
     * @param bool        $isVariadic
     */
    public function __construct(
        Function_ $function,
        string $name,
        ?string $typeHint,
        array $types,
        ?string $description,
        ?string $defaultValue,
        bool $isReference,
        bool $isOptional,
        bool $isVariadic
    ) {
        $this->id = (string) Uuid::uuid4();
        $this->function = $function;
        $this->name = $name;
        $this->typeHint = $typeHint;
        $this->types = $types;
        $this->description = $description;
        $this->defaultValue = $defaultValue;
        $this->isReference = $isReference;
        $this->isOptional = $isOptional;
        $this->isVariadic = $isVariadic;

        $this->function->addParameter($this);
    }

    /**
     * @return Function_
     */
    public function getFunction(): Function_
    {
        return $this->function;
    }
}
