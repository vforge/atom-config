<?php

namespace PhpIntegrator\Indexing\Structures;

use Ramsey\Uuid\Uuid;

/**
 * Represents a method parameter.
 */
class MethodParameter extends FunctionLikeParameter
{
    /**
     * @var Method
     */
    private $method;

    /**
     * @param Method      $method
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
        Method $method,
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
        $this->method = $method;
        $this->name = $name;
        $this->typeHint = $typeHint;
        $this->types = $types;
        $this->description = $description;
        $this->defaultValue = $defaultValue;
        $this->isReference = $isReference;
        $this->isOptional = $isOptional;
        $this->isVariadic = $isVariadic;

        $this->method->addParameter($this);
    }

    /**
     * @return Method
     */
    public function getMethod(): Method
    {
        return $this->method;
    }
}
