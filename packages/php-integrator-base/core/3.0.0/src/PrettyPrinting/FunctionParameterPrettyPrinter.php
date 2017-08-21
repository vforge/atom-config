<?php

namespace PhpIntegrator\PrettyPrinting;

/**
 * Pretty prints function and method parameters.
 */
class FunctionParameterPrettyPrinter
{
    /**
     * @var ParameterDefaultValuePrettyPrinter
     */
    private $parameterDefaultValuePrettyPrinter;

    /**
     * @var TypeListPrettyPrinter
     */
    private $typeListPrettyPrinter;

    /**
     * @var ParameterNamePrettyPrinter
     */
    private $parameterNamePrettyPrinter;

    /**
     * @param ParameterDefaultValuePrettyPrinter $parameterDefaultValuePrettyPrinter
     * @param TypeListPrettyPrinter              $typeListPrettyPrinter
     * @param ParameterNamePrettyPrinter         $parameterNamePrettyPrinter
     */
    public function __construct(
        ParameterDefaultValuePrettyPrinter $parameterDefaultValuePrettyPrinter,
        TypeListPrettyPrinter $typeListPrettyPrinter,
        ParameterNamePrettyPrinter $parameterNamePrettyPrinter
    ) {
        $this->parameterDefaultValuePrettyPrinter = $parameterDefaultValuePrettyPrinter;
        $this->typeListPrettyPrinter = $typeListPrettyPrinter;
        $this->parameterNamePrettyPrinter = $parameterNamePrettyPrinter;
    }

    /**
     * @param array $parameter
     *
     * @return string
     */
    public function print(array $parameter): string
    {
        $label = '';

        if (!empty($parameter['types'])) {
            $label .= $this->typeListPrettyPrinter->print(array_map(function (array $type) {
                return $type['type'];
            }, $parameter['types']));

            $label .= ' ';
        }

        $label .= $this->parameterNamePrettyPrinter->print($parameter);

        if ($parameter['defaultValue'] !== null) {
            $label .= ' = ' . $this->parameterDefaultValuePrettyPrinter->print($parameter['defaultValue']);
        }

        return $label;
    }
}
