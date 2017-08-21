<?php

namespace PhpIntegrator\Analysis\Conversion;

use ArrayAccess;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\Indexing\Structures\AccessModifierNameValue;

/**
 * Converts raw property data from the index to more useful data.
 */
class PropertyConverter extends AbstractConverter
{
    /**
     * @param Structures\Property $property
     * @param ArrayAccess         $class
     *
     * @return array
     */
    public function convertForClass(Structures\Property $property, ArrayAccess $class): array
    {
        $data = [
            'name'               => $property->getName(),
            'startLine'          => $property->getStartLine(),
            'endLine'            => $property->getEndLine(),
            'defaultValue'       => $property->getDefaultValue(),
            'isMagic'            => $property->getIsMagic(),
            'isPublic'           => $property->getAccessModifier()->getName() === AccessModifierNameValue::PUBLIC_,
            'isProtected'        => $property->getAccessModifier()->getName() === AccessModifierNameValue::PROTECTED_,
            'isPrivate'          => $property->getAccessModifier()->getName() === AccessModifierNameValue::PRIVATE_,
            'isStatic'           => $property->getIsStatic(),
            'isDeprecated'       => $property->getIsDeprecated(),
            'hasDocblock'        => $property->getHasDocblock(),
            'hasDocumentation'   => $property->getHasDocblock(),

            'shortDescription'  => $property->getShortDescription(),
            'longDescription'   => $property->getLongDescription(),
            'typeDescription'   => $property->getTypeDescription(),

            'types'             => $this->convertTypes($property->getTypes()),
        ];

        return array_merge($data, [
            'override'          => null,

            'declaringClass' => [
                'fqcn'            => $class['fqcn'],
                'filename'        => $class['filename'],
                'startLine'       => $class['startLine'],
                'endLine'         => $class['endLine'],
                'type'            => $class['type'],
            ],

            'declaringStructure' => [
                'fqcn'            => $class['fqcn'],
                'filename'        => $class['filename'],
                'startLine'       => $class['startLine'],
                'endLine'         => $class['endLine'],
                'type'            => $class['type'],
                'startLineMember' => $property->getStartLine(),
                'endLineMember'   => $property->getEndLine()
            ]
        ]);
    }
}
