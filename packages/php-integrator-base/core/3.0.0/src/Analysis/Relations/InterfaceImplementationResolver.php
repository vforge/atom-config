<?php

namespace PhpIntegrator\Analysis\Relations;

use ArrayObject;

/**
 * Deals with resolving implementation of interfaces for classlikes.
 *
 * "Implementation" in this context means nothing more than "is using an interface after the implements keyword". In
 * other words, it doesn't matter if the class is actually implementing the methods from the interface, as long as it's
 * (directly) referencing it, this class handles it.
 */
class InterfaceImplementationResolver extends AbstractResolver
{
    /**
     * @param ArrayObject $interface
     * @param ArrayObject $class
     *
     * @return void
     */
    public function resolveImplementationOf(ArrayObject $interface, ArrayObject $class): void
    {
        foreach ($interface['constants'] as $constant) {
            $this->resolveInheritanceOfConstant($constant, $class);
        }

        foreach ($interface['methods'] as $method) {
            $this->resolveImplementationOfMethod($method, $class);
        }
    }

    /**
     * @param array       $interfaceConstantData
     * @param ArrayObject $class
     *
     * @return void
     */
    protected function resolveInheritanceOfConstant(array $interfaceConstantData, ArrayObject $class): void
    {
        $class['constants'][$interfaceConstantData['name']] = $interfaceConstantData + [
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
                'startLineMember' => $interfaceConstantData['startLine'],
                'endLineMember'   => $interfaceConstantData['endLine']
            ]
        ];
    }

    /**
     * @param array       $interfaceMethodData
     * @param ArrayObject $class
     *
     * @return void
     */
    protected function resolveImplementationOfMethod(array $interfaceMethodData, ArrayObject $class): void
    {
        $childMethod = [];
        $inheritedData = [];

        if (isset($class['methods'][$interfaceMethodData['name']])) {
            $childMethod = $class['methods'][$interfaceMethodData['name']];

            if ($childMethod['declaringStructure']['type'] !== 'interface') {
                $childMethod['implementations'][] = [
                    'declaringClass'     => $interfaceMethodData['declaringClass'],
                    'declaringStructure' => $interfaceMethodData['declaringStructure'],
                    'startLine'          => $interfaceMethodData['startLine'],
                    'endLine'            => $interfaceMethodData['endLine']
                ];
            }

            if ($interfaceMethodData['hasDocumentation'] && $this->isInheritingFullDocumentation($childMethod)) {
                $inheritedData = $this->extractInheritedMethodInfo($interfaceMethodData, $childMethod);
            } elseif ($childMethod['longDescription'] !== null && $interfaceMethodData['longDescription'] !== null) {
                $inheritedData['longDescription'] = $this->resolveInheritDoc(
                    $childMethod['longDescription'],
                    $interfaceMethodData['longDescription']
                );
            }
        }

        $class['methods'][$interfaceMethodData['name']] = array_merge($interfaceMethodData, $childMethod, $inheritedData, [
            'declaringClass' => [
                'fqcn'     => $class['fqcn'],
                'filename' => $class['filename'],
                'startLine'=> $class['startLine'],
                'endLine'  => $class['endLine'],
                'type'     => $class['type']
            ]
        ]);
    }
}
