<?php

namespace PhpIntegrator\Analysis\Conversion;

use PhpIntegrator\Indexing\Structures;

/**
 * Converts raw function data from the index to more useful data.
 */
class FunctionConverter extends AbstractConverter
{
    /**
     * @param Structures\FunctionLike $function
     *
     * @return array
     */
    public function convert(Structures\FunctionLike $function): array
    {
        $parameters = [];

        foreach ($function->getParameters() as $parameter) {
            $parameters[] = [
                'name'         => $parameter->getName(),
                'typeHint'     => $parameter->getTypeHint(),
                'types'        => $this->convertTypes($parameter->getTypes()),
                'description'  => $parameter->getDescription(),
                'defaultValue' => $parameter->getDefaultValue(),
                'isReference'  => $parameter->getIsReference(),
                'isVariadic'   => $parameter->getIsVariadic(),
                'isOptional'   => $parameter->getIsOptional()
            ];
        }

        $throwsAssoc = [];

        foreach ($function->getThrows() as $throws) {
            $throwsAssoc[] = [
                'type'        => $throws->getFqcn(),
                'description' => $throws->getDescription()
            ];
        }

        $data = [
            'name'              => $function->getName(),
            'startLine'         => $function->getStartLine(),
            'endLine'           => $function->getEndLine(),
            'filename'          => $function->getFile()->getPath(),

            'parameters'        => $parameters,
            'throws'            => $throwsAssoc,
            'isDeprecated'      => $function->getIsDeprecated(),
            'hasDocblock'       => $function->getHasDocblock(),
            'hasDocumentation'  => $function->getHasDocblock(),

            'shortDescription'  => $function->getShortDescription(),
            'longDescription'   => $function->getLongDescription(),
            'returnDescription' => $function->getReturnDescription(),

            'returnTypeHint'    => $function->getReturnTypeHint(),
            'returnTypes'       => $this->convertTypes($function->getReturnTypes())
        ];

        if ($function instanceof Structures\Function_) {
            $data['fqcn'] = $function->getFqcn();
        }

        return $data;
    }
}
