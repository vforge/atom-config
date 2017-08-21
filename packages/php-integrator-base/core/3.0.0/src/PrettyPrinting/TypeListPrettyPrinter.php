<?php

namespace PhpIntegrator\PrettyPrinting;

/**
 * Pretty prints type lists.
 */
class TypeListPrettyPrinter
{
    /**
     * @var TypePrettyPrinter
     */
    private $typePrettyPrinter;

    /**
     * @param TypePrettyPrinter $typePrettyPrinter
     */
    public function __construct(TypePrettyPrinter $typePrettyPrinter)
    {
        $this->typePrettyPrinter = $typePrettyPrinter;
    }

    /**
     * @param string[] $types
     *
     * @return string
     */
    public function print(array $types): string
    {
        return implode('|', array_map(function (string $type) {
            return $this->typePrettyPrinter->print($type);
        }, $types));
    }
}
