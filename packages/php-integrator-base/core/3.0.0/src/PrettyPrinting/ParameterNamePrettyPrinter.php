<?php

namespace PhpIntegrator\PrettyPrinting;

/**
 * Pretty prints parameter names.
 */
class ParameterNamePrettyPrinter
{
    /**
     * @param array $parameter
     *
     * @return string
     */
    public function print(array $parameter): string
    {
        $label = '';

        if ($parameter['isVariadic']) {
            $label .= '...';
        }

        if ($parameter['isReference']) {
            $label .= '&';
        }

        return $label . '$' . $parameter['name'];
    }
}
