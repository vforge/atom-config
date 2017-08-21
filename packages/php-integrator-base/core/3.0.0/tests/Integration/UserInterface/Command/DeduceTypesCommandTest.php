<?php

namespace PhpIntegrator\Tests\Integration\UserInterface\Command;

use ReflectionClass;

use PhpIntegrator\Indexing\FileNotFoundStorageException;

use PhpIntegrator\UserInterface\Command\DeduceTypesCommand;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class DeduceTypesCommandTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testCorrectlyAnalyzesTypeOverrideAnnotations(): void
    {
        $output = $this->deduceTypesFromExpression('TypeOverrideAnnotations.phpt', '$a');

        $this->assertEquals(['\Traversable'], $output);

        $output = $this->deduceTypesFromExpression('TypeOverrideAnnotations.phpt', '$b');

        $this->assertEquals(['\Traversable'], $output);

        $output = $this->deduceTypesFromExpression('TypeOverrideAnnotations.phpt', '$c');

        $this->assertEquals(['\A\C', 'null'], $output);

        $output = $this->deduceTypesFromExpression('TypeOverrideAnnotations.phpt', '$d');

        $this->assertEquals(['\A\D'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyResolvesThisInClass(): void
    {
        $output = $this->deduceTypesFromExpression('ThisInClass.phpt', '$this');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyResolvesThisOutsideClass(): void
    {
        $output = $this->deduceTypesFromExpression('ThisOutsideClass.phpt', '$this');

        $this->assertEquals([], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesFunctionTypeHints(): void
    {
        $output = $this->deduceTypesFromExpression('FunctionParameterTypeHint.phpt', '$b');

        $this->assertEquals(['\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesNullableFunctionTypeHintsViaDefaultValue(): void
    {
        $output = $this->deduceTypesFromExpression('FunctionParameterTypeHintDefaultValue.phpt', '$b');

        $this->assertEquals(['\A\B', 'null'], $output);
    }
    /**
     * @return void
     */
    public function testCorrectlyAnalyzesNullableFunctionTypeHintsViaNullableSyntax(): void
    {
        $output = $this->deduceTypesFromExpression('FunctionParameterTypeHintNullableSyntax.phpt', '$b');

        $this->assertEquals(['\A\B', 'null'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesFunctionDocblocks(): void
    {
        $output = $this->deduceTypesFromExpression('FunctionParameterDocblock.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesMethodTypeHints(): void
    {
        $output = $this->deduceTypesFromExpression('MethodParameterTypeHint.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesMethodDocblocks(): void
    {
        $output = $this->deduceTypesFromExpression('MethodParameterDocblock.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesClosureTypeHints(): void
    {
        $output = $this->deduceTypesFromExpression('ClosureParameterTypeHint.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyMovesBeyondClosureScopeForVariableUses(): void
    {
        $output = $this->deduceTypesFromExpression('ClosureVariableUseStatement.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);

        $output = $this->deduceTypesFromExpression('ClosureVariableUseStatement.phpt', '$c');

        $this->assertEquals(['\A\C'], $output);

        $output = $this->deduceTypesFromExpression('ClosureVariableUseStatement.phpt', '$d');

        $this->assertEquals(['\A\D'], $output);

        $output = $this->deduceTypesFromExpression('ClosureVariableUseStatement.phpt', '$e');

        $this->assertEquals([], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesCatchBlockTypeHints(): void
    {
        $output = $this->deduceTypesFromExpression('CatchBlockTypeHint.phpt', '$e');

        $this->assertEquals(['\UnexpectedValueException'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesIfStatementWithInstanceof(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofIf.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesIfStatementWithInstanceofAndProperty(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofIfWithProperty.phpt', '$this->foo');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesIfStatementWithInstanceofAndPropertyWithParentKeyword(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofIfWithPropertyWithParentKeyword.phpt', 'parent::$foo');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesIfStatementWithInstanceofAndStaticPropertyWithClassName(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofIfWithStaticPropertyWithClassName.phpt', 'Test::$foo');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesIfStatementWithInstanceofAndStaticPropertyWithSelfKeyword(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofIfWithStaticPropertyWithSelfKeyword.phpt', 'self::$foo');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesIfStatementWithInstanceofAndStaticPropertyWithStaticKeyword(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofIfWithStaticPropertyWithStaticKeyword.phpt', 'static::$foo');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesComplexIfStatementWithInstanceofAndVariableInsideCondition(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofComplexIfVariableInsideCondition.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesComplexIfStatementWithInstanceofAndAnd(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofComplexIfAnd.phpt', '$b');

        $this->assertEquals(['\A\B', '\A\C', '\A\D'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesComplexIfStatementWithInstanceofAndOr(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofComplexIfOr.phpt', '$b');

        $this->assertEquals(['\A\B', '\A\C', '\A\D', '\A\E'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyIfStatementWithInstanceofAndOrTakesPrecedenceOverFunctionTypeHint(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofIfOrWithTypeHint.phpt', '$b');

        $this->assertEquals(['\A\B', '\A\C'], $output);
    }

    /**
     * @return void
     */
    public function testIfWithInstanceofContainingIfWithDifferentInstanceofGivesNestedTypePrecedenceOverFirst(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofNestedIf.phpt', '$b');

        $this->assertEquals(['\A\A'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesNestedIfStatementWithInstanceofAndNegation(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofNestedIfWithNegation.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesNestedIfStatementWithInstanceofAndReassignment(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofNestedIfReassignment.phpt', '$b');

        $this->assertEquals(['\A\A'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesIfStatementWithNotInstanceof(): void
    {
        $output = $this->deduceTypesFromExpression('IfNotInstanceof.phpt', '$b');

        $this->assertEquals(['\A\A'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesComplexIfStatementWithNotStrictlyEqualsNull(): void
    {
        $output = $this->deduceTypesFromExpression('IfNotStrictlyEqualsNull.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesComplexIfStatementWithNotLooselyEqualsNull(): void
    {
        $output = $this->deduceTypesFromExpression('IfNotLooselyEqualsNull.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesComplexIfStatementWithStrictlyEqualsNull(): void
    {
        $output = $this->deduceTypesFromExpression('IfStrictlyEqualsNull.phpt', '$b');

        $this->assertEquals(['null'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesComplexIfStatementWithLooselyEqualsNull(): void
    {
        $output = $this->deduceTypesFromExpression('IfLooselyEqualsNull.phpt', '$b');

        $this->assertEquals(['null'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesIfStatementWithTruthy(): void
    {
        $output = $this->deduceTypesFromExpression('IfTruthy.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesIfStatementWithFalsy(): void
    {
        $output = $this->deduceTypesFromExpression('IfFalsy.phpt', '$b');

        $this->assertEquals(['null'], $output);
    }

    /**
     * @return void
     */
    public function testTypeOverrideAnnotationsStillTakePrecedenceOverConditionals(): void
    {
        $output = $this->deduceTypesFromExpression('IfWithTypeOverride.phpt', '$b');

        $this->assertEquals(['string'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesComplexIfStatementWithVariableHandlingFunction(): void
    {
        $output = $this->deduceTypesFromExpression('IfVariableHandlingFunction.phpt', '$b');

        $this->assertEquals([
            'array',
            'bool',
            'callable',
            'float',
            'int',
            'null',
            'string',
            'object',
            'resource'
        ], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyTreatsIfConditionAsSeparateScope(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofIfSeparateScope.phpt', '$b');

        $this->assertEquals([], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesElseIfStatementWithInstanceof(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofElseIf.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testIfStatementCorrectlyNarrowsDownDetectedTypeOfStringVariable(): void
    {
        $output = $this->deduceTypesFromExpression('IfStatementNarrowsTypeOfStringVariable.phpt', '$b');

        $this->assertEquals(['string'], $output);
    }

    /**
     * @return void
     */
    public function testNestedIfStatementDoesNotExpandTypeListAgainIfPreviousIfStatementWasSpecific(): void
    {
        $output = $this->deduceTypesFromExpression('IfStatementDoesNotExpandTypeListOfVariable.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyConfinesTreatsElseIfConditionAsSeparateScope(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofElseIfSeparateScope.phpt', '$b');

        $this->assertEquals([], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesTernaryExpressionWithInstanceof(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofTernary.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyStartsFromTheDocblockTypeOfPropertiesBeforeApplyingConditionals(): void
    {
        $output = $this->deduceTypesFromExpression('IfWithProperty.phpt', '$b->foo');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyConfinesTreatsTernaryExpressionConditionAsSeparateScope(): void
    {
        $output = $this->deduceTypesFromExpression('InstanceofTernarySeparateScope.phpt', '$b');

        $this->assertEquals([], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesTernaryExpression(): void
    {
        $output = $this->deduceTypesFromExpression('TernaryExpression.phpt', '$a');

        $this->assertEquals(['\A'], $output);

        $output = $this->deduceTypesFromExpression('TernaryExpression.phpt', '$b');

        $this->assertEquals(['\B'], $output);

        $output = $this->deduceTypesFromExpression('TernaryExpression.phpt', '$c');

        $this->assertEquals(['\C', 'null'], $output);

        $output = $this->deduceTypesFromExpression('TernaryExpression.phpt', '$d');

        $this->assertEquals(['\A', '\C', 'null'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesForeach(): void
    {
        $output = $this->deduceTypesFromExpression('Foreach.phpt', '$a');

        $this->assertEquals(['\DateTime'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesForeachWithStaticMethodCallReturningArrayWithSelfObjects(): void
    {
        $output = $this->deduceTypesFromExpression('ForeachWithStaticMethodCallReturningArrayWithSelfObjects.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesForeachWithStaticMethodCallReturningArrayWithStaticObjects(): void
    {
        $output = $this->deduceTypesFromExpression('ForeachWithStaticMethodCallReturningArrayWithStaticObjects.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesAssignments(): void
    {
        $output = $this->deduceTypesFromExpression('Assignment.phpt', '$a');

        $this->assertEquals(['\DateTime'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyIgnoresAssignmentsOutOfScope(): void
    {
        $output = $this->deduceTypesFromExpression('AssignmentOutOfScope.phpt', '$a');

        $this->assertEquals(['\DateTime'], $output);
    }

    /**
     * @return void
     */
    public function testDocblockTakesPrecedenceOverTypeHint(): void
    {
        $output = $this->deduceTypesFromExpression('DocblockPrecedence.phpt', '$b');

        $this->assertEquals(['\B'], $output);
    }

    /**
     * @return void
     */
    public function testVariadicTypesForParametersAreCorrectlyAnalyzed(): void
    {
        $output = $this->deduceTypesFromExpression('FunctionVariadicParameter.phpt', '$b');

        $this->assertEquals(['\A\B[]'], $output);
    }

    /**
     * @return void
     */
    public function testSpecialTypesForParametersResolveCorrectly(): void
    {
        $output = $this->deduceTypesFromExpression('FunctionParameterTypeHintSpecial.phpt', '$a');

        $this->assertEquals(['\A\C'], $output);

        $output = $this->deduceTypesFromExpression('FunctionParameterTypeHintSpecial.phpt', '$b');

        $this->assertEquals(['\A\C'], $output);

        $output = $this->deduceTypesFromExpression('FunctionParameterTypeHintSpecial.phpt', '$c');

        $this->assertEquals(['\A\C'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesStaticPropertyAccess(): void
    {
        $result = $this->deduceTypesFromExpression(
            'StaticPropertyAccess.phpt',
            'Bar::$testProperty'
        );

        $this->assertEquals(['\DateTime'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesSelf(): void
    {
        $result = $this->deduceTypesFromExpression(
            'Self.phpt',
            'self::$testProperty'
        );

        $this->assertEquals(['\B'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesStatic(): void
    {
        $result = $this->deduceTypesFromExpression(
            'Static.phpt',
            'static::$testProperty'
        );

        $this->assertEquals(['\B'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesParent(): void
    {
        $result = $this->deduceTypesFromExpression(
            'Parent.phpt',
            'parent::$testProperty'
        );

        $this->assertEquals(['\B'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesThis(): void
    {
        $result = $this->deduceTypesFromExpression(
            'This.phpt',
            '$this->testProperty'
        );

        $this->assertEquals(['\B'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesVariables(): void
    {
        $result = $this->deduceTypesFromExpression(
            'Variable.phpt',
            '$var->testProperty'
        );

        $this->assertEquals(['\B'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesGlobalFunctions(): void
    {
        $result = $this->deduceTypesFromExpression(
            'GlobalFunction.phpt',
            '\global_function()'
        );

        $this->assertEquals(['\B', 'null'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesUnqualifiedGlobalFunctions(): void
    {
        $result = $this->deduceTypesFromExpression(
            'GlobalFunction.phpt',
            'global_function()'
        );

        $this->assertEquals(['\B', 'null'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesGlobalFunctionsInNamespace(): void
    {
        $result = $this->deduceTypesFromExpression(
            'GlobalFunctionInNamespace.phpt',
            '\N\global_function()'
        );

        $this->assertEquals(['\N\B', 'null'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesUnqualifiedGlobalFunctionsInNamespace(): void
    {
        $result = $this->deduceTypesFromExpression(
            'GlobalFunctionInNamespace.phpt',
            'global_function()'
        );

        $this->assertEquals(['\N\B', 'null'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesGlobalConstants(): void
    {
        $result = $this->deduceTypesFromExpression(
            'GlobalConstant.phpt',
            '\GLOBAL_CONSTANT'
        );

        $this->assertEquals(['string'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesUnqualifiedGlobalConstants(): void
    {
        $result = $this->deduceTypesFromExpression(
            'GlobalConstant.phpt',
            'GLOBAL_CONSTANT'
        );

        $this->assertEquals(['string'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesGlobalConstantsInNamespace(): void
    {
        $result = $this->deduceTypesFromExpression(
            'GlobalConstantInNamespace.phpt',
            '\N\GLOBAL_CONSTANT'
        );

        $this->assertEquals(['string'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesUnqualifiedGlobalConstantsInNamespace(): void
    {
        $result = $this->deduceTypesFromExpression(
            'GlobalConstantInNamespace.phpt',
            'GLOBAL_CONSTANT'
        );

        $this->assertEquals(['string'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesGlobalConstantsAssignedToOtherGlobalConstants(): void
    {
        $result = $this->deduceTypesFromExpression(
            'GlobalConstant.phpt',
            '\ANOTHER_GLOBAL_CONSTANT'
        );

        $this->assertEquals(['string'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesClosures(): void
    {
        $result = $this->deduceTypesFromExpression(
            'Closure.phpt',
            '$var'
        );

        $this->assertEquals(['\Closure'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesTypeOfElementsOfArrayWithObjects(): void
    {
        $output = $this->deduceTypesFromExpression('ArrayElementOfArrayWithObjects.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesTypeOfElementsOfString(): void
    {
        $output = $this->deduceTypesFromExpression('ArrayElementOfString.phpt', '$b');

        $this->assertEquals(['string'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesTypeOfElementsOfTypeNotAccessibleAsArray(): void
    {
        $output = $this->deduceTypesFromExpression('ArrayElementOfTypeNotAccessibleAsArray.phpt', '$b');

        $this->assertEquals(['mixed'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesTypeOfElementsOfArrayWithObjectsOfMultipleTypes(): void
    {
        $output = $this->deduceTypesFromExpression('ArrayElementOfArrayWithObjectsOfMultipleTypes.phpt', '$b');

        $this->assertEquals(['\A\B', '\A\C'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesTypeOfElementsOfArrayWithSelfElementsReturnedByStaticMethodCall(): void
    {
        $output = $this->deduceTypesFromExpression('ArrayElementOfArrayWithSelfElementsFromStaticMethodCall.phpt', '$b');

        $this->assertEquals(['\A\B'], $output);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesNewWithStatic(): void
    {
        $result = $this->deduceTypesFromExpression(
            'NewWithKeyword.phpt',
            'new static'
        );

        $this->assertEquals(['\Bar'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesNewWithSelf(): void
    {
        $result = $this->deduceTypesFromExpression(
            'NewWithKeyword.phpt',
            'new self'
        );

        $this->assertEquals(['\Bar'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesNewWithParent(): void
    {
        $result = $this->deduceTypesFromExpression(
            'NewWithKeyword.phpt',
            'new parent'
        );

        $this->assertEquals(['\Foo'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesClone(): void
    {
        $result = $this->deduceTypesFromExpression(
            'Clone.phpt',
            'clone $var'
        );

        $this->assertEquals(['\Bar'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesLongerChains(): void
    {
        $result = $this->deduceTypesFromExpression(
            'LongerChain.phpt',
            '$this->testProperty->aMethod()->anotherProperty'
        );

        $this->assertEquals(['\DateTime'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyAnalyzesScalarTypes(): void
    {
        $file = 'ScalarType.phpt';

        $this->assertEquals(['int'], $this->deduceTypesFromExpression($file, '5'));
        $this->assertEquals(['int'], $this->deduceTypesFromExpression($file, '05'));
        $this->assertEquals(['int'], $this->deduceTypesFromExpression($file, '0x5'));
        $this->assertEquals(['float'], $this->deduceTypesFromExpression($file, '5.5'));
        $this->assertEquals(['bool'], $this->deduceTypesFromExpression($file, 'true'));
        $this->assertEquals(['bool'], $this->deduceTypesFromExpression($file, 'false'));
        $this->assertEquals(['string'], $this->deduceTypesFromExpression($file, '"test"'));
        $this->assertEquals(['string'], $this->deduceTypesFromExpression($file, '\'test\''));
        $this->assertEquals(['array'], $this->deduceTypesFromExpression($file, '[$test1, function() {}]'));
        $this->assertEquals(['array'], $this->deduceTypesFromExpression($file, 'array($test1, function() {})'));

        $this->assertEquals(['string'], $this->deduceTypesFromExpression($file, '"
            test
        "'));

        $this->assertEquals(['string'], $this->deduceTypesFromExpression($file, '\'
            test
        \''));
    }

    /**
     * @return void
     */
    public function testCorrectlyProcessesSelfAssign(): void
    {
        $result = $this->deduceTypesFromExpression(
            'SelfAssign.phpt',
            '$foo1'
        );

        $this->assertEquals(['\A\Foo'], $result);

        $result = $this->deduceTypesFromExpression(
            'SelfAssign.phpt',
            '$foo2'
        );

        $this->assertEquals(['\A\Foo'], $result);

        $result = $this->deduceTypesFromExpression(
            'SelfAssign.phpt',
            '$foo3'
        );

        $this->assertEquals(['\A\Foo'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyProcessesStaticMethodCallAssignedToVariableWithFqcnWithLeadingSlash(): void
    {
        $result = $this->deduceTypesFromExpression(
            'StaticMethodCallFqcnLeadingSlash.phpt',
            '$data'
        );

        $this->assertEquals(['\A\B'], $result);
    }

    /**
     * @return void
     */
    public function testCorrectlyReturnsMultipleTypes(): void
    {
        $result = $this->deduceTypesFromExpression(
            'MultipleTypes.phpt',
            '$this->testProperty'
        );

        $this->assertEquals([
            'string',
            'int',
            '\Foo',
            '\Bar'
        ], $result);
    }

    /**
     * @return void
     */
    public function testVariableInCatchBlockWithMultipleExceptionTypeHintsHasMultipleTypes(): void
    {
        $result = $this->deduceTypesFromExpression(
            'CatchMultipleExceptionTypes.phpt',
            '$e'
        );

        $this->assertEquals([
            '\Exception',
            '\Throwable'
        ], $result);
    }

    /**
     * @return void
     */
    public function testIgnoreLastElement(): void
    {
        $result = $this->deduceTypesFromExpression(
            'AssignmentIgnoreLastElement.phpt',
            '$a->test',
            true
        );

        $this->assertEquals(['\DateTime'], $result);
    }

    /**
     * @return void
     */
    public function testMetaStaticMethodTypesWithMatchingFqcn(): void
    {
        $result = $this->deduceTypesFromExpressionWithMeta(
            'MetaStaticMethodTypesMatchingFqcn.phpt',
            'MetaStaticMethodTypesMetaFile.phpt',
            '$var'
        );

        $this->assertEquals(['\B\Bar'], $result);
    }

    /**
     * @return void
     */
    public function testThrowsExceptionWhenFileIsNotInIndex(): void
    {
        $command = $this->container->get('deduceTypesCommand');

        $this->expectException(FileNotFoundStorageException::class);

        $command->deduceTypes('DoesNotExist.phpt', 'Code', 'CodeWithExpression', 1, false);
    }

    /**
     * @param string $file
     * @param string $expression
     * @param bool   $ignoreLastElement
     *
     * @return string[]
     */
    protected function deduceTypesFromExpression(string $file, string $expression, bool $ignoreLastElement = false): array
    {
        $path = __DIR__ . '/DeduceTypesCommandTest/' . $file;

        $markerOffset = $this->getMarkerOffset($path, '<MARKER>');

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('deduceTypesCommand');

        $reflectionClass = new ReflectionClass(DeduceTypesCommand::class);
        $reflectionMethod = $reflectionClass->getMethod('deduceTypesFromExpression');
        $reflectionMethod->setAccessible(true);

        $file = $this->container->get('storage')->getFileByPath($path);

        return $reflectionMethod->invoke($command, $file, file_get_contents($path), $expression, $markerOffset, $ignoreLastElement);
    }

    /**
     * @param string $file
     * @param string $metaFile
     * @param string $expression
     *
     * @return array
     */
    protected function deduceTypesFromExpressionWithMeta(string $file, string $metaFile, string $expression): array
    {
        $path = __DIR__ . '/DeduceTypesCommandTest/' . $file;
        $metaFilePath = __DIR__ . '/DeduceTypesCommandTest/' . $metaFile;

        $markerOffset = $this->getMarkerOffset($path, '<MARKER>');

        $this->indexTestFile($this->container, $metaFilePath);
        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('deduceTypesCommand');

        $reflectionClass = new ReflectionClass(DeduceTypesCommand::class);
        $reflectionMethod = $reflectionClass->getMethod('deduceTypesFromExpression');
        $reflectionMethod->setAccessible(true);

        $file = $this->container->get('storage')->getFileByPath($path);

        return $reflectionMethod->invoke($command, $file, file_get_contents($path), $expression, $markerOffset, false);
    }

    /**
     * @param string $path
     * @param string $marker
     *
     * @return int
     */
    protected function getMarkerOffset(string $path, string $marker): int
    {
        $testFileContents = @file_get_contents($path);

        $markerOffset = mb_strpos($testFileContents, $marker);

        return $markerOffset;
    }
}
