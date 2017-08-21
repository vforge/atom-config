<?php

namespace PhpIntegrator\Tooltips;

use PhpIntegrator\PrettyPrinting\TypeListPrettyPrinter;

/**
 * Pretty prints type lists for use in tooltips.
 */
class TooltipTypeListPrettyPrinter
{
    /**
     * @var TypeListPrettyPrinter
     */
    private $typeListPrettyPrinter;

    /**
     * @param TypeListPrettyPrinter $typeListPrettyPrinter
     */
    public function __construct(TypeListPrettyPrinter $typeListPrettyPrinter)
    {
        $this->typeListPrettyPrinter = $typeListPrettyPrinter;
    }

    /**
     * @param string[] $types
     *
     * @return string
     */
    public function print(array $types): string
    {
        if (empty($types)) {
            return '(Not known)';
        }

        $value = $this->typeListPrettyPrinter->print($types);

        return str_replace('|', '&#124;', $value);
    }
}
