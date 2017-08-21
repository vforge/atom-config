<?php

namespace PhpIntegrator\Analysis\Conversion;

use ArrayAccess;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\Indexing\Structures\AccessModifierNameValue;

/**
 * Converts raw class constant data from the index to more useful data.
 */
class ClasslikeConstantConverter extends ConstantConverter
{
    /**
     * @param Structures\ClassConstant $constant
     * @param ArrayAccess              $class
     *
     * @return array
     */
    public function convertForClass(Structures\ClassConstant $constant, ArrayAccess $class): array
    {
        $data = parent::convert($constant);

        return array_merge($data, [
            'isPublic'          => $constant->getAccessModifier() ? $constant->getAccessModifier()->getName() === AccessModifierNameValue::PUBLIC_ : true,
            'isProtected'       => $constant->getAccessModifier() ? $constant->getAccessModifier()->getName() === AccessModifierNameValue::PROTECTED_ : false,
            'isPrivate'         => $constant->getAccessModifier() ? $constant->getAccessModifier()->getName() === AccessModifierNameValue::PRIVATE_ : false,

            'declaringClass' => [
                'fqcn'      => $class['fqcn'],
                'filename'  => $class['filename'],
                'startLine' => $class['startLine'],
                'endLine'   => $class['endLine'],
                'type'      => $class['type']
            ],

            'declaringStructure' => [
                'fqcn'            => $class['fqcn'],
                'filename'        => $class['filename'],
                'startLine'       => $class['startLine'],
                'endLine'         => $class['endLine'],
                'type'            => $class['type'],
                'startLineMember' => $constant->getStartLine(),
                'endLineMember'   => $constant->getEndLine()
            ]
        ]);
    }
}
