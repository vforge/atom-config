<?php

namespace PhpIntegrator\Tests\Unit\Analysis\Typing;

use PhpIntegrator\Analysis\ClasslikeInfoBuilder;

use PhpIntegrator\Analysis\Typing\ParameterDocblockTypeSemanticEqualityChecker;

use PhpIntegrator\DocblockTypeParser;

use PhpIntegrator\NameQualificationUtilities\PositionalNameResolverInterface;
use PhpIntegrator\NameQualificationUtilities\StructureAwareNameResolverFactoryInterface;

class ParameterDocblockTypeSemanticEqualityCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMatchingTypeNamePasses(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'int'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\IntDocblockType(),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('int');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testMismatchingTypeNameFails(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'bool'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\IntDocblockType(),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('bool');
        $this->assertFalse($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testMatchingNullableTypePasses(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => true,
            'type'        => 'int'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\CompoundDocblockType(
                new DocblockTypeParser\IntDocblockType(),
                new DocblockTypeParser\NullDocblockType()
            ),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('int');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testMismatchingNullableTypeFails(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => true,
            'type'        => 'int'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\IntDocblockType(),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('int');
        $this->assertFalse($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testClassTypesWithSameQualificationPasses(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'A'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\ClassDocblockType('A'),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('\A', '\A');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testClassTypesWithDifferentQualificationPasses(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'A'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\ClassDocblockType('\A\B'),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('\B\A', '\B\A');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testDifferentClassTypesFails(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock([
                [
                    'fqcn'       => '\A'
                ],
                [
                    'fqcn'       => '\A',
                    'parents'    => [],
                    'interfaces' => []
                ]
            ])
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'A'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\ClassDocblockType('B'),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('\A', '\B');
        $this->assertFalse($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testClassTypeAllowsSpecializationByParent(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock([
                [
                    'fqcn'       => '\A'
                ],
                [
                    'fqcn'       => '\B',
                    'parents'    => ['\A'],
                    'interfaces' => []
                ]
            ])
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'A'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\ClassDocblockType('B'),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('\A', '\B');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testClassTypeAllowsSpecializationByInterface(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock([
                [
                    'fqcn'       => '\A'
                ],
                [
                    'fqcn'       => '\B',
                    'parents'    => [],
                    'interfaces' => ['\A']
                ]
            ])
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'A'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\ClassDocblockType('B'),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('\A', '\B');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testClassTypeAllowsMultipleSpecializationsByParentOrInterface(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock([
                [
                    'fqcn'       => '\A'
                ],
                [
                    'fqcn'       => '\B',
                    'parents'    => ['\A'],
                    'interfaces' => []
                ],
                [
                    'fqcn'       => '\A'
                ],
                [
                    'fqcn'       => '\C',
                    'parents'    => [],
                    'interfaces' => ['\A']
                ]
            ])
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'A'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\CompoundDocblockType(
                new DocblockTypeParser\ClassDocblockType('B'),
                new DocblockTypeParser\ClassDocblockType('C')
            ),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('\A', '\B', '\C');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testVariadicParameterRequiresArrayTypeHintAndPassesWhenItIsPresent(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock([
                [
                    'fqcn'       => '\A'
                ],
                [
                    'fqcn'       => '\A',
                    'parents'    => [],
                    'interfaces' => []
                ]
            ])
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => true,
            'isNullable'  => false,
            'type'        => 'A'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\SpecializedArrayDocblockType(
                new DocblockTypeParser\ClassDocblockType('A')
            ),
            'description' => null,
            'isVariadic'  => true,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('\A', '\A');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testVariadicParameterRequiresArrayTypeHintAndailsWhenItIsMissing(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock([
                [
                    'fqcn'       => '\A'
                ],
                [
                    'fqcn'       => '\A',
                    'parents'    => [],
                    'interfaces' => []
                ]
            ])
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => true,
            'isNullable'  => false,
            'type'        => 'A'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\ClassDocblockType('A'),
            'description' => null,
            'isVariadic'  => true,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('\A', '\A');
        $this->assertFalse($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testVariadicParameterWithDifferentQualification(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock([
                [
                    'fqcn'       => '\B\A'
                ],
                [
                    'fqcn'       => '\B\A',
                    'parents'    => [],
                    'interfaces' => []
                ]
            ])
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => true,
            'isNullable'  => false,
            'type'        => 'A'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\SpecializedArrayDocblockType(
                new DocblockTypeParser\ClassDocblockType('\B\A')
            ),
            'description' => null,
            'isVariadic'  => true,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('\B\A', '\B\A');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testArrayTypeAllowsSpecialization(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'array'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\SpecializedArrayDocblockType(
                new DocblockTypeParser\IntDocblockType()
            ),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('array');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testArrayTypeDoesNotAllowOtherTypes(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'array'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\IntDocblockType(),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('array');
        $this->assertFalse($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testNullableArrayTypeWithMatchingNullabilityPasses(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => true,
            'type'        => 'array'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\CompoundDocblockType(
                new DocblockTypeParser\ArrayDocblockType(),
                new DocblockTypeParser\NullDocblockType()
            ),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('array');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testNullableArrayTypeWithMismatchingNullabilityFails(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => true,
            'type'        => 'array'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\ArrayDocblockType(),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('array');
        $this->assertFalse($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testArrayTypeWithMismatchingNullabilityFails(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'array'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\CompoundDocblockType(
                new DocblockTypeParser\ArrayDocblockType(),
                new DocblockTypeParser\NullDocblockType()
            ),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('array');
        $this->assertFalse($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testNullableArrayTypeAllowsSpecialization(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => true,
            'type'        => 'array'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\CompoundDocblockType(
                new DocblockTypeParser\SpecializedArrayDocblockType(
                    new DocblockTypeParser\IntDocblockType()
                ),
                new DocblockTypeParser\NullDocblockType()
            ),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('array');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testArrayTypeAllowsMultipleSpecializations(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'array'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\CompoundDocblockType(
                new DocblockTypeParser\SpecializedArrayDocblockType(
                    new DocblockTypeParser\IntDocblockType()
                ),
                new DocblockTypeParser\SpecializedArrayDocblockType(
                    new DocblockTypeParser\FloatDocblockType()
                )
            ),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('array');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testArrayTypeAllowsMultipleSpecializationsButFailsWhenAnotherTypeIsPresent(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'array'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\CompoundDocblockType(
                new DocblockTypeParser\SpecializedArrayDocblockType(
                    new DocblockTypeParser\IntDocblockType()
                ),
                new DocblockTypeParser\SpecializedArrayDocblockType(
                    new DocblockTypeParser\FloatDocblockType()
                ),
                new DocblockTypeParser\BoolDocblockType()
            ),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('array');
        $this->assertFalse($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testArrayTypeAllowsMultipleSpecializationsButFailsWhenAnotherTypeIsPresentAndThatTypeIsNull(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'array'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\CompoundDocblockType(
                new DocblockTypeParser\SpecializedArrayDocblockType(
                    new DocblockTypeParser\IntDocblockType()
                ),
                new DocblockTypeParser\SpecializedArrayDocblockType(
                    new DocblockTypeParser\FloatDocblockType()
                ),
                new DocblockTypeParser\NullDocblockType()
            ),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('array');
        $this->assertFalse($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testNullableArrayTypeAllowsMultipleSpecializations(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => true,
            'type'        => 'array'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\CompoundDocblockType(
                new DocblockTypeParser\SpecializedArrayDocblockType(
                    new DocblockTypeParser\IntDocblockType()
                ),
                new DocblockTypeParser\SpecializedArrayDocblockType(
                    new DocblockTypeParser\FloatDocblockType()
                ),
                new DocblockTypeParser\NullDocblockType()
            ),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('array');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testNullableArrayTypeAllowsMultipleSpecializationsButFailsWhenNullIsMissing(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => true,
            'type'        => 'array'
        ];

        $docblockParameter = [
            'type'        => 'int[]|float[]',
            'type'        => new DocblockTypeParser\CompoundDocblockType(
                new DocblockTypeParser\SpecializedArrayDocblockType(
                    new DocblockTypeParser\IntDocblockType()
                ),
                new DocblockTypeParser\SpecializedArrayDocblockType(
                    new DocblockTypeParser\FloatDocblockType()
                )
            ),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('array');
        $this->assertFalse($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testMatchingReferenceTypesPasses(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => true,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'int'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\IntDocblockType(),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => true
        ];

        $resolver->method('resolve')->willReturn('int');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testMismatchingReferenceTypesFails(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => true,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'int'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\IntDocblockType(),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('int');
        $this->assertFalse($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testArrayTypeWithParanthesizedSpecialization(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'array'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\SpecializedArrayDocblockType(
                new DocblockTypeParser\CompoundDocblockType(
                    new DocblockTypeParser\IntDocblockType(),
                    new DocblockTypeParser\FloatDocblockType()
                )
            ),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('array');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testNullableArrayTypeWithParanthesizedSpecialization(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => true,
            'type'        => 'array'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\CompoundDocblockType(
                new DocblockTypeParser\SpecializedArrayDocblockType(
                    new DocblockTypeParser\CompoundDocblockType(
                        new DocblockTypeParser\IntDocblockType(),
                        new DocblockTypeParser\FloatDocblockType()
                    )
                ),
                new DocblockTypeParser\NullDocblockType()
            ),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('array');
        $this->assertTrue($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @return void
     */
    public function testMismatchingClassTypeAndStringDocblockTypeFailsButDoesNotGenerateError(): void
    {
        $resolver = $this->getMockBuilder(PositionalNameResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();

        $checker = new ParameterDocblockTypeSemanticEqualityChecker(
            $this->mockStructureAwareNameResolverFactory($resolver),
            $this->getClasslikeInfoBuilderMock()
        );

        $parameter = [
            'isReference' => false,
            'isVariadic'  => false,
            'isNullable'  => false,
            'type'        => 'Foo'
        ];

        $docblockParameter = [
            'type'        => new DocblockTypeParser\StringDocblockType(),
            'description' => null,
            'isVariadic'  => false,
            'isReference' => false
        ];

        $resolver->method('resolve')->willReturn('Foo');
        $this->assertFalse($checker->isEqual($parameter, $docblockParameter, 'ignored', 1));
    }

    /**
     * @param PositionalNameResolverInterface $structureAwareNameResolverMock
     *
     * @return StructureAwareNameResolverFactoryInterface
     */
    protected function mockStructureAwareNameResolverFactory(
        PositionalNameResolverInterface $structureAwareNameResolverMock
    ): StructureAwareNameResolverFactoryInterface {
        $resolver = $this->getMockBuilder(StructureAwareNameResolverFactoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $resolver->method('create')->will($this->returnValue($structureAwareNameResolverMock));

        return $resolver;
    }

    /**
     * @param mixed[] $returnValues
     *
     * @return ClasslikeInfoBuilder
     */
    protected function getClasslikeInfoBuilderMock(array $returnValues = []): ClasslikeInfoBuilder
    {
        $classlikeInfoBuilder = $this->getMockBuilder(ClasslikeInfoBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getClasslikeInfo'])
            ->getMock();

        if (!empty($returnValues)) {
            $classlikeInfoBuilder->method('getClasslikeInfo')->willReturn(...$returnValues);
        }

        return $classlikeInfoBuilder;
    }
}
