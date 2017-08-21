<?php

namespace PhpIntegrator\Tests\Integration\UserInterface\Command;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class ClassInfoCommandTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testLeadingSlashIsResolvedCorrectly(): void
    {
        $fileName = 'SimpleClass.phpt';

        $this->assertEquals(
            $this->getClassInfo($fileName, 'A\SimpleClass'),
            $this->getClassInfo($fileName, '\A\SimpleClass')
        );
    }

    /**
     * @return void
     */
    public function testDataIsCorrectForASimpleClass(): void
    {
        $fileName = 'SimpleClass.phpt';

        $output = $this->getClassInfo($fileName, 'A\SimpleClass');

        $this->assertEquals([
            'name'               => 'SimpleClass',
            'fqcn'               => '\A\SimpleClass',
            'startLine'          => 10,
            'endLine'            => 13,
            'filename'           => $this->getPathFor($fileName),
            'type'               => 'class',
            'isAbstract'         => false,
            'isFinal'            => false,
            'isDeprecated'       => false,
            'isAnnotation'       => false,
            'hasDocblock'        => true,
            'hasDocumentation'   => true,
            'shortDescription'   => 'This is the summary.',
            'longDescription'    => 'This is a long description.',
            'parents'            => [],
            'interfaces'         => [],
            'traits'             => [],
            'directParents'      => [],
            'directInterfaces'   => [],
            'directTraits'       => [],
            'directChildren'     => [],
            'directImplementors' => [],
            'directTraitUsers'   => [],
            'constants'          => [
                'class' => [
                    'name'               => 'class',
                    'startLine'          => 10,
                    'endLine'            => 10,
                    'defaultValue'       => '\'A\SimpleClass\'',
                    'filename'           => $this->getPathFor($fileName),
                    'isPublic'           => true,
                    'isProtected'        => false,
                    'isPrivate'          => false,
                    'isStatic'           => true,
                    'isDeprecated'       => false,
                    'hasDocblock'        => false,
                    'hasDocumentation'   => false,

                    'shortDescription'   => 'PHP built-in class constant that evaluates to the FQCN.',
                    'longDescription'    => null,
                    'typeDescription'    => null,

                    'types'             => [
                        [
                            'type'         => 'string',
                            'fqcn'         => 'string',
                            'resolvedType' => 'string'
                        ]
                    ],

                    'declaringClass'     => [
                        'fqcn'      => '\A\SimpleClass',
                        'filename'  => $this->getPathFor($fileName),
                        'startLine' => 10,
                        'endLine'   => 13,
                        'type'      => 'class'
                    ],

                    'declaringStructure' => [
                        'fqcn'            => '\A\SimpleClass',
                        'filename'        => $this->getPathFor($fileName),
                        'startLine'       => 10,
                        'endLine'         => 13,
                        'type'            => 'class',
                        'startLineMember' => 10,
                        'endLineMember'   => 10
                    ]
                ]
            ],
            'properties'         => [],
            'methods'            => []
        ], $output);
    }

    /**
     * @return void
     */
    public function testDataIsCorrectForClassProperties(): void
    {
        $fileName = 'ClassProperty.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertEquals([
            'name'               => 'testProperty',
            'startLine'          => 14,
            'endLine'            => 14,
            'defaultValue'       => "'test'",
            'isMagic'            => false,
            'isPublic'           => false,
            'isProtected'        => true,
            'isPrivate'          => false,
            'isStatic'           => false,
            'isDeprecated'       => false,
            'hasDocblock'        => true,
            'hasDocumentation'   => true,
            'shortDescription'   => 'This is the summary.',
            'longDescription'    => 'This is a long description.',
            'typeDescription'    => null,

            'types'             => [
                [
                    'type'         => 'MyType',
                    'fqcn'         => '\A\MyType',
                    'resolvedType' => '\A\MyType'
                ],

                [
                    'type'         => 'string',
                    'fqcn'         => 'string',
                    'resolvedType' => 'string'
                ]
            ],

            'override'           => null,

            'declaringClass' => [
                'fqcn'      => '\A\TestClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 5,
                'endLine'   => 15,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\TestClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 5,
                'endLine'         => 15,
                'type'            => 'class',
                'startLineMember' => 14,
                'endLineMember'   => 14
            ]
        ], $output['properties']['testProperty']);
    }

    /**
     * @return void
     */
    public function testDataIsCorrectForClassMethods(): void
    {
        $fileName = 'ClassMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertEquals([
            'name'               => 'testMethod',
            'startLine'          => 19,
            'endLine'            => 22,
            'filename'           => $this->getPathFor($fileName),

            'parameters'         => [
                [
                    'name'         => 'firstParameter',
                    'typeHint'     => '\DateTimeInterface',
                    'description'  => 'First parameter description.',
                    'defaultValue' => 'null',
                    'isReference'  => false,
                    'isVariadic'   => false,
                    'isOptional'   => true,

                    'types' => [
                        [
                            'type'         => '\DateTimeInterface',
                            'fqcn'         => '\DateTimeInterface',
                            'resolvedType' => '\DateTimeInterface'
                        ],

                        [
                            'type'         => '\DateTime',
                            'fqcn'         => '\DateTime',
                            'resolvedType' => '\DateTime'
                        ]
                    ]
                ],

                [
                    'name'         => 'secondParameter',
                    'typeHint'     => null,
                    'description'  => null,
                    'defaultValue' => 'true',
                    'isReference'  => true,
                    'isVariadic'   => false,
                    'isOptional'   => true,
                    'types'        => [
                        [
                            'type'         => 'bool',
                            'fqcn'         => 'bool',
                            'resolvedType' => 'bool'
                        ]
                    ]
                ],

                [
                    'name'         => 'thirdParameter',
                    'typeHint'     => null,
                    'description'  => null,
                    'defaultValue' => null,
                    'isReference'  => false,
                    'isVariadic'   => true,
                    'isOptional'   => false,
                    'types'        => []
                ]
            ],

            'throws'             => [
                [
                    'type'        => '\UnexpectedValueException',
                    'description' => 'when something goes wrong.'
                ],

                [
                    'type'        => '\LogicException',
                    'description' => 'when something is wrong.'
                ]
            ],

            'isDeprecated'       => false,
            'hasDocblock'        => true,
            'hasDocumentation'   => true,

            'shortDescription'   => 'This is the summary.',
            'longDescription'    => 'This is a long description.',
            'returnDescription'  => null,
            'returnTypeHint'     => null,

            'returnTypes' => [
                [
                    'type'         => 'mixed',
                    'fqcn'         => 'mixed',
                    'resolvedType' => 'mixed'
                ],

                [
                    'type'         => 'bool',
                    'fqcn'         => 'bool',
                    'resolvedType' => 'bool'
                ]
            ],

            'isMagic'            => false,
            'isPublic'           => true,
            'isProtected'        => false,
            'isPrivate'          => false,
            'isStatic'           => false,
            'isAbstract'         => false,
            'isFinal'            => false,
            'override'           => null,
            'implementations'    => [],

            'declaringClass'     => [
                'fqcn'      => '\A\TestClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 5,
                'endLine'   => 23,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\TestClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 5,
                'endLine'         => 23,
                'type'            => 'class',
                'startLineMember' => 19,
                'endLineMember'   => 22
            ]
        ], $output['methods']['testMethod']);
    }

    /**
     * @return void
     */
    public function testDataIsCorrectForClassConstants(): void
    {
        $fileName = 'ClassConstant.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertEquals($output['constants']['TEST_CONSTANT'], [
            'name'               => 'TEST_CONSTANT',
            'startLine'          => 14,
            'endLine'            => 14,
            'defaultValue'       => '5',
            'filename'           => $this->getPathFor($fileName),
            'isPublic'           => true,
            'isProtected'        => false,
            'isPrivate'          => false,
            'isStatic'           => true,
            'isDeprecated'       => false,
            'hasDocblock'        => true,
            'hasDocumentation'   => true,

            'shortDescription'   => 'This is the summary.',
            'longDescription'    => 'This is a long description.',
            'typeDescription'    => null,

            'types'             => [
                [
                    'type'         => 'MyType',
                    'fqcn'         => '\A\MyType',
                    'resolvedType' => '\A\MyType'
                ],

                [
                    'type'         => 'string',
                    'fqcn'         => 'string',
                    'resolvedType' => 'string'
                ]
            ],

            'declaringClass'     => [
                'fqcn'      => '\A\TestClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 5,
                'endLine'   => 15,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\TestClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 5,
                'endLine'         => 15,
                'type'            => 'class',
                'startLineMember' => 14,
                'endLineMember'   => 14
            ]
        ]);
    }

    /**
     * @return void
     */
    public function testDocblockInheritanceWorksProperlyForClasses(): void
    {
        $fileName = 'ClassDocblockInheritance.phpt';

        $childClassOutput = $this->getClassInfo($fileName, 'A\ChildClass');
        $parentClassOutput = $this->getClassInfo($fileName, 'A\ParentClass');
        $anotherChildClassOutput = $this->getClassInfo($fileName, 'A\AnotherChildClass');

        $this->assertEquals('This is the summary.', $childClassOutput['shortDescription']);
        $this->assertEquals('This is a long description.', $childClassOutput['longDescription']);

        $this->assertEquals(
            'Pre. ' . $parentClassOutput['longDescription'] . ' Post.',
            $anotherChildClassOutput['longDescription']
        );
    }

    /**
     * @return void
     */
    public function testDocblockInheritanceWorksProperlyForMethods(): void
    {
        $fileName = 'MethodDocblockInheritance.phpt';

        $traitOutput       = $this->getClassInfo($fileName, 'A\TestTrait');
        $interfaceOutput   = $this->getClassInfo($fileName, 'A\TestInterface');
        $childClassOutput  = $this->getClassInfo($fileName, 'A\ChildClass');
        $parentClassOutput = $this->getClassInfo($fileName, 'A\ParentClass');

        $keysToTestForEquality = [
            'hasDocumentation',
            'isDeprecated',
            'longDescription',
            'shortDescription',
            'returnTypes',
            'parameters',
            'throws'
        ];

        foreach ($keysToTestForEquality as $key) {
            $this->assertEquals(
                $childClassOutput['methods']['basicDocblockInheritanceTraitTest'][$key],
                $traitOutput['methods']['basicDocblockInheritanceTraitTest'][$key]
            );

            $this->assertEquals(
                $childClassOutput['methods']['basicDocblockInheritanceInterfaceTest'][$key],
                $interfaceOutput['methods']['basicDocblockInheritanceInterfaceTest'][$key]
            );

            $this->assertEquals(
                $childClassOutput['methods']['basicDocblockInheritanceBaseClassTest'][$key],
                $parentClassOutput['methods']['basicDocblockInheritanceBaseClassTest'][$key]
            );
        }

        $this->assertEquals(
            'Pre. ' . $parentClassOutput['methods']['inheritDocBaseClassTest']['longDescription'] . ' Post.',
            $childClassOutput['methods']['inheritDocBaseClassTest']['longDescription']
        );

        $this->assertEquals(
            'Pre. ' . $interfaceOutput['methods']['inheritDocInterfaceTest']['longDescription'] . ' Post.',
            $childClassOutput['methods']['inheritDocInterfaceTest']['longDescription']
        );

        $this->assertEquals(
            'Pre. ' . $traitOutput['methods']['inheritDocTraitTest']['longDescription'] . ' Post.',
            $childClassOutput['methods']['inheritDocTraitTest']['longDescription']
        );
    }

    /**
     * @return void
     */
    public function testDocblockInheritanceWorksProperlyForProperties(): void
    {
        $fileName = 'PropertyDocblockInheritance.phpt';

        $traitOutput       = $this->getClassInfo($fileName, 'A\TestTrait');
        $childClassOutput  = $this->getClassInfo($fileName, 'A\ChildClass');
        $parentClassOutput = $this->getClassInfo($fileName, 'A\ParentClass');

        $keysToTestForEquality = [
            'hasDocumentation',
            'isDeprecated',
            'shortDescription',
            'longDescription',
            'typeDescription',
            'types'
        ];

        foreach ($keysToTestForEquality as $key) {
            $this->assertEquals(
                $childClassOutput['properties']['basicDocblockInheritanceTraitTest'][$key],
                $traitOutput['properties']['basicDocblockInheritanceTraitTest'][$key]
            );

            $this->assertEquals(
                $childClassOutput['properties']['basicDocblockInheritanceBaseClassTest'][$key],
                $parentClassOutput['properties']['basicDocblockInheritanceBaseClassTest'][$key]
            );
        }

        $this->assertEquals(
            $childClassOutput['properties']['inheritDocBaseClassTest']['longDescription'],
            'Pre. ' . $parentClassOutput['properties']['inheritDocBaseClassTest']['longDescription'] . ' Post.'
        );

        $this->assertEquals(
            $childClassOutput['properties']['inheritDocTraitTest']['longDescription'],
            'Pre. ' . $traitOutput['properties']['inheritDocTraitTest']['longDescription'] . ' Post.'
        );
    }

    /**
     * @return void
     */
    public function testMethodOverridingIsAnalyzedCorrectly(): void
    {
        $fileName = 'MethodOverride.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        $this->assertEquals([
            [
                'name'         => 'foo',
                'typeHint'     => '\A\Foo',
                'description'  => null,
                'defaultValue' => null,
                'isReference'  => false,
                'isVariadic'   => false,
                'isOptional'   => false,

                'types' => [
                    [
                        'type'         => 'Foo',
                        'fqcn'         => '\A\Foo',
                        'resolvedType' => '\A\Foo'
                    ]
                ]
            ]
        ], $output['methods']['__construct']['parameters']);

        $this->assertEquals([
            'startLine'   => 25,
            'endLine'     => 28,
            'wasAbstract' => false,

            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 21,
                'endLine'   => 39,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 21,
                'endLine'         => 39,
                'type'            => 'class',
                'startLineMember' => 25,
                'endLineMember'   => 28
            ]
        ], $output['methods']['__construct']['override']);

        $this->assertEquals(55, $output['methods']['__construct']['startLine']);
        $this->assertEquals(58, $output['methods']['__construct']['endLine']);

        $this->assertEquals([
            [
                'name'         => 'foo',
                'typeHint'     => '\A\Foo',
                'description'  => null,
                'defaultValue' => 'null',
                'isReference'  => false,
                'isVariadic'   => false,
                'isOptional'   => true,

                'types' => [
                    [
                        'type'         => 'Foo',
                        'fqcn'         => '\A\Foo',
                        'resolvedType' => '\A\Foo'
                    ],

                    [
                        'type'         => 'null',
                        'fqcn'         => 'null',
                        'resolvedType' => 'null'
                    ]
                ]
            ]
        ], $output['methods']['parentTraitMethod']['parameters']);

        $this->assertEquals([
            'startLine'   => 15,
            'endLine'     => 18,
            'wasAbstract' => false,

            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 21,
                'endLine'   => 39,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentTrait',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 13,
                'endLine'         => 19,
                'type'            => 'trait',
                'startLineMember' => 15,
                'endLineMember'   => 18
            ]
        ], $output['methods']['parentTraitMethod']['override']);

        $this->assertEquals(65, $output['methods']['parentTraitMethod']['startLine']);
        $this->assertEquals(68, $output['methods']['parentTraitMethod']['endLine']);

        $this->assertEquals([
            [
                'name'         => 'foo',
                'typeHint'     => '\A\Foo',
                'description'  => null,
                'defaultValue' => 'null',
                'isReference'  => false,
                'isVariadic'   => false,
                'isOptional'   => true,

                'types' => [
                    [
                        'type'         => 'Foo',
                        'fqcn'         => '\A\Foo',
                        'resolvedType' => '\A\Foo'
                    ],

                    [
                        'type'         => 'null',
                        'fqcn'         => 'null',
                        'resolvedType' => 'null'
                    ]
                ]
            ]
        ], $output['methods']['parentMethod']['parameters']);

        $this->assertEquals([
            'startLine'   => 30,
            'endLine'     => 33,
            'wasAbstract' => false,

            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 21,
                'endLine'   => 39,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 21,
                'endLine'         => 39,
                'type'            => 'class',
                'startLineMember' => 30,
                'endLineMember'   => 33
            ]
        ], $output['methods']['parentMethod']['override']);

        $this->assertEquals(70, $output['methods']['parentMethod']['startLine']);
        $this->assertEquals(73, $output['methods']['parentMethod']['endLine']);

        $this->assertEquals([
            'startLine'   => 35,
            'endLine'     => 38,
            'wasAbstract' => false,

            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 21,
                'endLine'   => 39,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 21,
                'endLine'         => 39,
                'type'            => 'class',
                'startLineMember' => 35,
                'endLineMember'   => 38
            ]
        ], $output['methods']['ancestorMethod']['override']);

        $this->assertEquals(60, $output['methods']['ancestorMethod']['startLine']);
        $this->assertEquals(63, $output['methods']['ancestorMethod']['endLine']);

        $this->assertEquals([
            [
                'name'         => 'foo',
                'typeHint'     => '\A\Foo',
                'description'  => null,
                'defaultValue' => 'null',
                'isReference'  => false,
                'isVariadic'   => false,
                'isOptional'   => true,

                'types' => [
                    [
                        'type'         => 'Foo',
                        'fqcn'         => '\A\Foo',
                        'resolvedType' => '\A\Foo'
                    ],

                    [
                        'type'         => 'null',
                        'fqcn'         => 'null',
                        'resolvedType' => 'null'
                    ]
                ]
            ]
        ], $output['methods']['traitMethod']['parameters']);

        $this->assertEquals([
            'startLine'   => 43,
            'endLine'     => 46,
            'wasAbstract' => false,

            'declaringClass' => [
                'fqcn'      => '\A\TestTrait',
                'filename'  =>  $this->getPathFor($fileName),
                'startLine' => 41,
                'endLine'   => 49,
                'type'      => 'trait'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\TestTrait',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 41,
                'endLine'         => 49,
                'type'            => 'trait',
                'startLineMember' => 43,
                'endLineMember'   => 46
            ]
        ], $output['methods']['traitMethod']['override']);

        $this->assertEquals(75, $output['methods']['traitMethod']['startLine']);
        $this->assertEquals(78, $output['methods']['traitMethod']['endLine']);

        $this->assertEquals([
            [
                'name'         => 'foo',
                'typeHint'     => '\A\Foo',
                'defaultValue' => 'null',
                'description'  => null,
                'isReference'  => false,
                'isVariadic'   => false,
                'isOptional'   => true,

                'types' => [
                    [
                        'type'         => 'Foo',
                        'fqcn'         => '\A\Foo',
                        'resolvedType' => '\A\Foo'
                    ],

                    [
                        'type'         => 'null',
                        'fqcn'         => 'null',
                        'resolvedType' => 'null'
                    ]
                ]
            ]
        ], $output['methods']['abstractMethod']['parameters']);

        $this->assertEquals($output['methods']['abstractMethod']['override']['wasAbstract'], true);
    }

    /**
     * @return void
     */
    public function testMethodOverridingOfParentImplementationIsAnalyzedCorrectly(): void
    {
        $fileName = 'MethodOverrideOfParentImplementation.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        $this->assertEquals([
            'startLine'   => 12,
            'endLine'     => 15,
            'wasAbstract' => false,

            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  =>  $this->getPathFor($fileName),
                'startLine' => 10,
                'endLine'   => 16,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 10,
                'endLine'         => 16,
                'type'            => 'class',
                'startLineMember' => 12,
                'endLineMember'   => 15
            ]
        ], $output['methods']['interfaceMethod']['override']);

        $this->assertEmpty($output['methods']['interfaceMethod']['implementations']);

        $this->assertEquals(20, $output['methods']['interfaceMethod']['startLine']);
        $this->assertEquals(23, $output['methods']['interfaceMethod']['endLine']);
    }

    /**
     * @return void
     */
    public function testMethodOverridingAndImplementationSimultaneouslyIsAnalyzedCorrectly(): void
    {
        $fileName = 'MethodOverrideAndImplementation.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        $this->assertEquals([
            [
                'startLine'   => 7,
                'endLine'     => 7,

                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface',
                    'filename'  =>  $this->getPathFor($fileName),
                    'startLine' => 5,
                    'endLine'   => 8,
                    'type'      => 'interface'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ]
            ]
        ], $output['methods']['interfaceMethod']['implementations']);

        $this->assertEquals([
            'startLine'   => 12,
            'endLine'     => 15,
            'wasAbstract' => false,

            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  =>  $this->getPathFor($fileName),
                'startLine' => 10,
                'endLine'   => 16,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 10,
                'endLine'         => 16,
                'type'            => 'class',
                'startLineMember' => 12,
                'endLineMember'   => 15
            ]
        ], $output['methods']['interfaceMethod']['override']);

        $this->assertEquals(20, $output['methods']['interfaceMethod']['startLine']);
        $this->assertEquals(23, $output['methods']['interfaceMethod']['endLine']);
    }

    /**
     * @return void
     */
    public function testPropertyOverridingIsAnalyzedCorrectly(): void
    {
        $fileName = 'PropertyOverride.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        $this->assertEquals([
            'startLine' => 12,
            'endLine'   => 12,

            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 15,
                'endLine'   => 21,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentTrait',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 10,
                'endLine'         => 13,
                'type'            => 'trait',
                'startLineMember' => 12,
                'endLineMember'   => 12
            ]
        ], $output['properties']['parentTraitProperty']['override']);

        $this->assertEquals([
            'startLine' => 19,
            'endLine'   => 19,

            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 15,
                'endLine'   => 21,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 15,
                'endLine'         => 21,
                'type'            => 'class',
                'startLineMember' => 19,
                'endLineMember'   => 19
            ]
        ], $output['properties']['parentProperty']['override']);

        $this->assertEquals([
            'startLine' => 20,
            'endLine'   => 20,

            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 15,
                'endLine'   => 21,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 15,
                'endLine'         => 21,
                'type'            => 'class',
                'startLineMember' => 20,
                'endLineMember'   => 20
            ]
        ], $output['properties']['ancestorProperty']['override']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationIsAnalyzedCorrectlyWhenImplementingMethodFromInterfaceReferencedByParentClass(): void
    {
        $fileName = 'MethodImplementationFromParentClassInterface.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        $this->assertEquals([
            [
                'name'         => 'foo',
                'typeHint'     => '\A\Foo',
                'defaultValue' => 'null',
                'description'  => null,
                'isReference'  => false,
                'isVariadic'   => false,
                'isOptional'   => true,

                'types' => [
                    [
                        'type'         => 'Foo',
                        'fqcn'         => '\A\Foo',
                        'resolvedType' => '\A\Foo'
                    ],

                    [
                        'type'         => 'null',
                        'fqcn'         => 'null',
                        'resolvedType' => 'null'
                    ]
                ]
            ]
        ], $output['methods']['parentInterfaceMethod']['parameters']);

        $this->assertEquals([
            [
                'startLine' => 7,
                'endLine'   => 7,

                'declaringClass' => [
                    'fqcn'      => '\A\ParentClass',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 10,
                    'endLine'   => 13,
                    'type'      => 'class'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\ParentInterface',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ]
            ]
        ], $output['methods']['parentInterfaceMethod']['implementations']);

        $this->assertEquals('\A\ChildClass', $output['methods']['parentInterfaceMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\ChildClass', $output['methods']['parentInterfaceMethod']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationIsAnalyzedCorrectlyWhenImplementingMethodFromInterfaceParent(): void
    {
        $fileName = 'MethodImplementationFromInterfaceParent.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        $this->assertEquals([
            [
                'startLine' => 7,
                'endLine'   => 7,

                'declaringClass' => [
                    'fqcn'      => '\A\ParentInterface',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 5,
                    'endLine'   => 8,
                    'type'      => 'interface'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\ParentInterface',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ]
            ]
        ], $output['methods']['interfaceParentMethod']['implementations']);

        $this->assertNull($output['methods']['interfaceParentMethod']['override']);

        $this->assertEquals('\A\ChildClass', $output['methods']['interfaceParentMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\ChildClass', $output['methods']['interfaceParentMethod']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationIsAnalyzedCorrectlyWhenImplementingMethodFromInterfaceDirectlyReferenced(): void
    {
        $fileName = 'MethodImplementationFromDirectInterface.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        $this->assertEquals([
            [
                'startLine' => 7,
                'endLine'   => 7,

                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 5,
                    'endLine'   => 8,
                    'type'      => 'interface'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ]
            ]
        ], $output['methods']['interfaceMethod']['implementations']);

        $this->assertEquals('\A\ChildClass', $output['methods']['interfaceMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\ChildClass', $output['methods']['interfaceMethod']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testMethodParameterTypeIsCorrectlyDeducedIfParameterIsVariadic(): void
    {
        $fileName = 'MethodVariadicParameter.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');
        $parameters = $output['methods']['testMethod']['parameters'];

        $this->assertEquals('\stdClass[]', $parameters[0]['types'][0]['type']);
    }

    /**
     * @return void
     */
    public function testDataIsCorrectForClassInheritance(): void
    {
        $fileName = 'ClassInheritance.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        $this->assertEquals($output['parents'], ['\A\BaseClass', '\A\AncestorClass']);
        $this->assertEquals($output['directParents'], ['\A\BaseClass']);

        $this->assertThat($output['constants'], $this->arrayHasKey('INHERITED_CONSTANT'));
        $this->assertThat($output['constants'], $this->arrayHasKey('CHILD_CONSTANT'));

        $this->assertThat($output['properties'], $this->arrayHasKey('inheritedProperty'));
        $this->assertThat($output['properties'], $this->arrayHasKey('childProperty'));

        $this->assertThat($output['methods'], $this->arrayHasKey('inheritedMethod'));
        $this->assertThat($output['methods'], $this->arrayHasKey('childMethod'));

        // Do a couple of sanity checks.
        $this->assertEquals('\A\BaseClass', $output['constants']['INHERITED_CONSTANT']['declaringClass']['fqcn']);
        $this->assertEquals('\A\BaseClass', $output['properties']['inheritedProperty']['declaringClass']['fqcn']);
        $this->assertEquals('\A\BaseClass', $output['methods']['inheritedMethod']['declaringClass']['fqcn']);

        $this->assertEquals('\A\BaseClass', $output['constants']['INHERITED_CONSTANT']['declaringStructure']['fqcn']);
        $this->assertEquals('\A\BaseClass', $output['properties']['inheritedProperty']['declaringStructure']['fqcn']);
        $this->assertEquals('\A\BaseClass', $output['methods']['inheritedMethod']['declaringStructure']['fqcn']);

        $output = $this->getClassInfo($fileName, 'A\BaseClass');

        $this->assertEquals($output['directChildren'], ['\A\ChildClass']);
        $this->assertEquals($output['parents'], ['\A\AncestorClass']);
    }

    /**
     * @return void
     */
    public function testInterfaceImplementationIsCorrectlyProcessed(): void
    {
        $fileName = 'InterfaceImplementation.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertEquals(['\A\BaseInterface', '\A\FirstInterface', '\A\SecondInterface'], $output['interfaces']);
        $this->assertEquals(['\A\FirstInterface', '\A\SecondInterface'], $output['directInterfaces']);

        $this->assertThat($output['constants'], $this->arrayHasKey('FIRST_INTERFACE_CONSTANT'));
        $this->assertThat($output['constants'], $this->arrayHasKey('SECOND_INTERFACE_CONSTANT'));

        $this->assertThat($output['methods'], $this->arrayHasKey('methodFromFirstInterface'));
        $this->assertThat($output['methods'], $this->arrayHasKey('methodFromSecondInterface'));

        // Do a couple of sanity checks.
        $this->assertEquals('\A\FirstInterface', $output['constants']['FIRST_INTERFACE_CONSTANT']['declaringClass']['fqcn']);
        $this->assertEquals('\A\FirstInterface', $output['constants']['FIRST_INTERFACE_CONSTANT']['declaringStructure']['fqcn']);
        $this->assertEquals('\A\TestClass', $output['methods']['methodFromFirstInterface']['declaringClass']['fqcn']);
        $this->assertEquals('\A\FirstInterface', $output['methods']['methodFromFirstInterface']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\FirstInterface', $output['constants']['FIRST_INTERFACE_CONSTANT']['declaringClass']['fqcn']);
        $this->assertEquals('\A\FirstInterface', $output['constants']['FIRST_INTERFACE_CONSTANT']['declaringStructure']['fqcn']);
        $this->assertEquals('\A\TestClass', $output['methods']['methodFromFirstInterface']['declaringClass']['fqcn']);
        $this->assertEquals('\A\FirstInterface', $output['methods']['methodFromFirstInterface']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testClassTraitUsageIsCorrectlyProcessed(): void
    {
        $fileName = 'ClassTraitUsage.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertEquals(['\A\FirstTrait', '\A\SecondTrait', '\A\BaseTrait'], $output['traits']);
        $this->assertEquals(['\A\FirstTrait', '\A\SecondTrait'], $output['directTraits']);

        $this->assertThat($output['properties'], $this->arrayHasKey('baseTraitProperty'));
        $this->assertThat($output['properties'], $this->arrayHasKey('firstTraitProperty'));
        $this->assertThat($output['properties'], $this->arrayHasKey('secondTraitProperty'));

        $this->assertThat($output['methods'], $this->arrayHasKey('testAmbiguous'));
        $this->assertThat($output['methods'], $this->arrayHasKey('testAmbiguousAsWell'));
        $this->assertThat($output['methods'], $this->arrayHasKey('baseTraitMethod'));

        // Do a couple of sanity checks.
        $this->assertEquals('\A\BaseClass', $output['properties']['baseTraitProperty']['declaringClass']['fqcn']);
        $this->assertEquals('\A\BaseTrait', $output['properties']['baseTraitProperty']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\TestClass', $output['properties']['firstTraitProperty']['declaringClass']['fqcn']);
        $this->assertEquals('\A\FirstTrait', $output['properties']['firstTraitProperty']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\BaseClass', $output['methods']['baseTraitMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\BaseTrait', $output['methods']['baseTraitMethod']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\TestClass', $output['methods']['test1']['declaringClass']['fqcn']);
        $this->assertEquals('\A\FirstTrait', $output['methods']['test1']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\TestClass', $output['methods']['overriddenInBaseAndChild']['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestClass', $output['methods']['overriddenInBaseAndChild']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\TestClass', $output['methods']['overriddenInChild']['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestClass', $output['methods']['overriddenInChild']['declaringStructure']['fqcn']);

        // Test the 'as' keyword for renaming trait method.
        $this->assertThat($output['methods'], $this->arrayHasKey('test1'));
        $this->assertThat($output['methods'], $this->logicalNot($this->arrayHasKey('test')));

        $this->assertTrue($output['methods']['test1']['isPrivate']);

        $this->assertEquals('\A\TestClass', $output['methods']['testAmbiguous']['declaringClass']['fqcn']);
        $this->assertEquals('\A\SecondTrait', $output['methods']['testAmbiguous']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\TestClass', $output['methods']['testAmbiguousAsWell']['declaringClass']['fqcn']);
        $this->assertEquals('\A\FirstTrait', $output['methods']['testAmbiguousAsWell']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testClassTraitAliasWithoutAccessModifier(): void
    {
        $fileName = 'ClassTraitAliasWithoutAccessModifier.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertFalse($output['methods']['test1']['isPublic']);
        $this->assertTrue($output['methods']['test1']['isProtected']);
        $this->assertFalse($output['methods']['test1']['isPrivate']);
    }

    /**
     * @return void
     */
    public function testClassTraitAliasWithAccessModifier(): void
    {
        $fileName = 'ClassTraitAliasWithAccessModifier.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertFalse($output['methods']['test1']['isPublic']);
        $this->assertFalse($output['methods']['test1']['isProtected']);
        $this->assertTrue($output['methods']['test1']['isPrivate']);
    }

    /**
     * @return void
     */
    public function testMethodOverrideDataIsCorrectWhenClassHasMethodThatIsAlsoDefinedByOneOfItsOwnTraits(): void
    {
        $fileName = 'ClassOverridesOwnTraitMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertEquals('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestClass', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\TestTrait', $output['methods']['someMethod']['override']['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestTrait', $output['methods']['someMethod']['override']['declaringStructure']['fqcn']);

        $this->assertEmpty($output['methods']['someMethod']['implementations']);
    }

    /**
     * @return void
     */
    public function testMethodOverrideDataIsCorrectWhenClassHasMethodThatIsAlsoDefinedByOneOfItsOwnTraitsAndByTheParent(): void
    {
        $fileName = 'ClassOverridesTraitAndParentMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertEquals('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestClass', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\BaseClass', $output['methods']['someMethod']['override']['declaringClass']['fqcn']);
        $this->assertEquals('\A\BaseClass', $output['methods']['someMethod']['override']['declaringStructure']['fqcn']);

        $this->assertEmpty($output['methods']['someMethod']['implementations']);
    }

    /**
     * @return void
     */
    public function testTraitTraitUsageIsCorrectlyProcessed(): void
    {
        $fileName = 'TraitTraitUsage.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestTrait');

        $this->assertEquals(['\A\FirstTrait', '\A\SecondTrait'], $output['traits']);
        $this->assertEquals(['\A\FirstTrait', '\A\SecondTrait'], $output['directTraits']);

        $this->assertThat($output['properties'], $this->arrayHasKey('firstTraitProperty'));
        $this->assertThat($output['properties'], $this->arrayHasKey('secondTraitProperty'));

        $this->assertThat($output['methods'], $this->arrayHasKey('testAmbiguous'));
        $this->assertThat($output['methods'], $this->arrayHasKey('testAmbiguousAsWell'));

        // Do a couple of sanity checks.
        $this->assertEquals('\A\TestTrait', $output['properties']['firstTraitProperty']['declaringClass']['fqcn']);
        $this->assertEquals('\A\FirstTrait', $output['properties']['firstTraitProperty']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\TestTrait', $output['methods']['test1']['declaringClass']['fqcn']);
        $this->assertEquals('\A\FirstTrait', $output['methods']['test1']['declaringStructure']['fqcn']);

        // Test the 'as' keyword for renaming trait method.
        $this->assertThat($output['methods'], $this->arrayHasKey('test1'));
        $this->assertThat($output['methods'], $this->logicalNot($this->arrayHasKey('test')));

        $this->assertTrue($output['methods']['test1']['isPrivate']);

        $this->assertEquals('\A\TestTrait', $output['methods']['testAmbiguous']['declaringClass']['fqcn']);
        $this->assertEquals('\A\SecondTrait', $output['methods']['testAmbiguous']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\TestTrait', $output['methods']['testAmbiguousAsWell']['declaringClass']['fqcn']);
        $this->assertEquals('\A\FirstTrait', $output['methods']['testAmbiguousAsWell']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testTraitTraitAliasWithoutAccessModifier(): void
    {
        $fileName = 'TraitTraitAliasWithoutAccessModifier.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestTrait');

        $this->assertFalse($output['methods']['test1']['isPublic']);
        $this->assertTrue($output['methods']['test1']['isProtected']);
        $this->assertFalse($output['methods']['test1']['isPrivate']);
    }

    /**
     * @return void
     */
    public function testTraitTraitAliasWithAccessModifier(): void
    {
        $fileName = 'TraitTraitAliasWithAccessModifier.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestTrait');

        $this->assertFalse($output['methods']['test1']['isPublic']);
        $this->assertFalse($output['methods']['test1']['isProtected']);
        $this->assertTrue($output['methods']['test1']['isPrivate']);
    }

    /**
     * @return void
     */
    public function testMethodOverrideDataIsCorrectWhenTraitHasMethodThatIsAlsoDefinedByOneOfItsOwnTraits(): void
    {
        $fileName = 'TraitOverridesOwnTraitMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestTrait');

        $this->assertEquals('\A\TestTrait', $output['methods']['someMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestTrait', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\FirstTrait', $output['methods']['someMethod']['override']['declaringClass']['fqcn']);
        $this->assertEquals('\A\FirstTrait', $output['methods']['someMethod']['override']['declaringStructure']['fqcn']);

        $this->assertEmpty($output['methods']['someMethod']['implementations']);
    }

    /**
     * @return void
     */
    public function testMethodOverrideDataIsCorrectWhenInterfaceOverridesParentInterfaceMethod(): void
    {
        $fileName = 'InterfaceOverridesParentInterfaceMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestInterface');

        $this->assertEquals('\A\TestInterface', $output['methods']['interfaceMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestInterface', $output['methods']['interfaceMethod']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\BaseInterface', $output['methods']['interfaceMethod']['override']['declaringClass']['fqcn']);
        $this->assertEquals('\A\BaseInterface', $output['methods']['interfaceMethod']['override']['declaringStructure']['fqcn']);

        $this->assertEmpty($output['methods']['interfaceMethod']['implementations']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationDataIsCorrectWhenTraitMethodIndirectlyImplementsInterfaceMethod(): void
    {
        $fileName = 'TraitImplementsInterfaceMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertEquals('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestTrait', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        $this->assertEquals('\A\TestInterface', $output['methods']['someMethod']['implementations'][0]['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestInterface', $output['methods']['someMethod']['implementations'][0]['declaringStructure']['fqcn']);

        $this->assertNull($output['methods']['someMethod']['override']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationDataIsCorrectWhenClassReceivesSameInterfaceMethodFromTwoInterfacesAndDoesNotImplementMethod(): void
    {
        $fileName = 'ClassWithTwoInterfacesWithSameMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertEquals('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestInterface1', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        $this->assertEmpty($output['methods']['someMethod']['implementations']);

        $this->assertNull($output['methods']['someMethod']['override']);
    }

    /**
     * @return void
     */
    public function testMethodDeclaringStructureIsCorrectWhenMethodDirectlyOriginatesFromTrait(): void
    {
        $fileName = 'ClassUsingTraitMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertEquals('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestTrait', $output['methods']['someMethod']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationDataIsCorrectWhenClassMethodImplementsMultipleInterfaceMethodsSimultaneously(): void
    {
        $fileName = 'ClassMethodImplementsMultipleInterfaceMethods.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertEquals('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestClass', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        $this->assertEquals([
            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface1',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 5,
                    'endLine'   => 8,
                    'type'      => 'interface'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface1',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ],

                'startLine' => 7,
                'endLine'   => 7
            ],

            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface2',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 10,
                    'endLine'   => 13,
                    'type'      => 'interface',
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface2',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 10,
                    'endLine'         => 13,
                    'type'            => 'interface',
                    'startLineMember' => 12,
                    'endLineMember'   => 12
                ],

                'startLine' => 12,
                'endLine'   => 12
            ]
        ], $output['methods']['someMethod']['implementations']);

        $this->assertNull($output['methods']['someMethod']['override']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationDataIsCorrectWhenClassTraitMethodImplementsMultipleInterfaceMethodsSimultaneously(): void
    {
        $fileName = 'ClassTraitMethodImplementsMultipleInterfaceMethods.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertEquals('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestTrait', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        $this->assertEquals([
            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface1',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 5,
                    'endLine'   => 8,
                    'type'      => 'interface'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface1',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ],

                'startLine' => 7,
                'endLine'   => 7
            ],

            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface2',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 10,
                    'endLine'   => 13,
                    'type'      => 'interface',
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface2',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 10,
                    'endLine'         => 13,
                    'type'            => 'interface',
                    'startLineMember' => 12,
                    'endLineMember'   => 12
                ],

                'startLine' => 12,
                'endLine'   => 12
            ]
        ], $output['methods']['someMethod']['implementations']);

        $this->assertNull($output['methods']['someMethod']['override']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationDataIsCorrectWhenClassMethodImplementsMultipleDirectAndIndirectInterfaceMethodsSimultaneously(): void
    {
        $fileName = 'ClassMethodImplementsMultipleDirectAndIndirectInterfaceMethods.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        $this->assertEquals('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        $this->assertEquals('\A\TestClass', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        $this->assertEquals([
            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface1',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 5,
                    'endLine'   => 8,
                    'type'      => 'interface'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface1',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ],

                'startLine' => 7,
                'endLine'   => 7
            ],

            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface2',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 10,
                    'endLine'   => 13,
                    'type'      => 'interface',
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface2',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 10,
                    'endLine'         => 13,
                    'type'            => 'interface',
                    'startLineMember' => 12,
                    'endLineMember'   => 12
                ],

                'startLine' => 12,
                'endLine'   => 12
            ]
        ], $output['methods']['someMethod']['implementations']);

        $this->assertNull($output['methods']['someMethod']['override']);
    }

    /**
     * @return void
     */
    public function testSpecialTypesAreCorrectlyResolved(): void
    {
        $fileName = 'ResolveSpecialTypes.phpt';

        $output = $this->getClassInfo($fileName, 'A\childClass');

        $this->assertEquals([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['properties']['basePropSelf']['types']);

        $this->assertEquals([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['properties']['basePropStatic']['types']);

        $this->assertEquals([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['properties']['basePropThis']['types']);

        $this->assertEquals([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['properties']['propSelf']['types']);

        $this->assertEquals([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['properties']['propStatic']['types']);

        $this->assertEquals([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['properties']['propThis']['types']);

        $this->assertEquals([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['methods']['baseMethodSelf']['returnTypes']);

        $this->assertEquals([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['baseMethodStatic']['returnTypes']);

        $this->assertEquals([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['baseMethodThis']['returnTypes']);

        $this->assertEquals([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['methodSelf']['returnTypes']);

        $this->assertEquals([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['methodStatic']['returnTypes']);

        $this->assertEquals([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['methodThis']['returnTypes']);

        $this->assertEquals([
            [
                'type'         => 'childClass',
                'fqcn'         => '\A\childClass',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['methodOwnClassName']['returnTypes']);

        $this->assertEquals([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['methods']['baseMethodWithParameters']['parameters'][0]['types']);

        $this->assertEquals([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['baseMethodWithParameters']['parameters'][1]['types']);

        $this->assertEquals([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['baseMethodWithParameters']['parameters'][2]['types']);

        $output = $this->getClassInfo($fileName, 'A\ParentClass');

        $this->assertEquals([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['properties']['basePropSelf']['types']);

        $this->assertEquals([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['properties']['basePropStatic']['types']);

        $this->assertEquals([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['properties']['basePropThis']['types']);

        $this->assertEquals([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['methods']['baseMethodSelf']['returnTypes']);

        $this->assertEquals([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['methods']['baseMethodStatic']['returnTypes']);

        $this->assertEquals([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['methods']['baseMethodThis']['returnTypes']);
    }

    /**
     * @return void
     */
    public function testSkipsInterfaceImplementedTwice(): void
    {
        $fileName = 'InterfaceImplementedTwice.phpt';

        $output = $this->getClassInfo($fileName, '\A\TestClass');

        $this->assertEquals(['\A\I'], $output['interfaces']);
    }

    /**
     * @return void
     */
    public function testSkipsTraitUsedTwice(): void
    {
        $fileName = 'TraitUsedTwice.phpt';

        $output = $this->getClassInfo($fileName, '\A\TestClass');

        $this->assertEquals(['\A\T', '\A\T2'], $output['traits']);
    }

    /**
     * @return void
     */
    public function testSkipsInterfaceExtendedTwice(): void
    {
        $fileName = 'InterfaceExtendedTwice.phpt';

        $output = $this->getClassInfo($fileName, '\A\TestInterface');

        $this->assertEquals(['\A\I'], $output['parents']);
    }

    /**
     * @return void
     */
    public function testUnresolvedReturnType(): void
    {
        $fileName = 'UnresolvedReturnType.phpt';

        $output = $this->getClassInfo($fileName, '\A\TestClass');

        $this->assertEquals([
            [
                'type'         => 'DateTime',
                'fqcn'         => '\DateTime',
                'resolvedType' => '\DateTime'
            ]
        ], $output['methods']['foo']['returnTypes']);
    }

    /**
     * @expectedException \UnexpectedValueException
     *
     * @return void
     */
    public function testFailsOnUnknownClass(): void
    {
        $output = $this->getClassInfo('SimpleClass.phpt', 'DoesNotExist');
    }

    /**
     * @return void
     */
    public function testCircularDependencyWithClassExtendingItselfDoesNotLoop(): void
    {
        $fileName = 'CircularDependencyExtends.phpt';

        $this->assertNotNull($this->getClassInfo($fileName, 'A\C'));
    }

    /**
     * @return void
     */
    public function testCircularDependencyWithClassImplementingItselfDoesNotLoop(): void
    {
        $fileName = 'CircularDependencyImplements.phpt';

        $this->assertNotNull($this->getClassInfo($fileName, 'A\C'));
    }

    /**
     * @return void
     */
    public function testCircularDependencyWithClassUsingItselfAsTraitDoesNotLoop(): void
    {
        $fileName = 'CircularDependencyUses.phpt';

        $this->assertNotNull($this->getClassInfo($fileName, 'A\C'));
    }

    /**
     * @param string $file
     * @param string $fqcn
     *
     * @return array
     */
    protected function getClassInfo(string $file, string $fqcn): array
    {
        $path = $this->getPathFor($file);

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('classInfoCommand');

        return $command->getClassInfo($fqcn);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getPathFor(string $file): string
    {
        return __DIR__ . '/ClassInfoCommandTest/' . $file;
    }
}
