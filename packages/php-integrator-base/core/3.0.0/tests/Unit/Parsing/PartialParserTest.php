<?php

namespace PhpIntegrator\Tests\Unit\Parsing;

use PhpIntegrator\Parsing\PartialParser;

use PhpIntegrator\Parsing\Node\Expr;

use PhpParser\Node;
use PhpParser\Lexer;
use PhpParser\ParserFactory;

class PartialParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return ParserFactory
     */
    protected function createParserFactoryStub(): ParserFactory
    {
        return new ParserFactory();
    }

    /**
     * @return PartialParser
     */
    protected function createPartialParser(): PartialParser
    {
        return new PartialParser($this->createParserFactoryStub(), new Lexer());
    }

    /**
     * @return void
     */
    public function testParsesFunctionCalls(): void
    {
        $source = <<<'SOURCE'
<?php

array_walk
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\ConstFetch::class, $result->expr);
        $this->assertEquals('array_walk', $result->expr->name->toString());
    }

    /**
     * @return void
     */
    public function testParsesStaticConstFetches(): void
    {
        $source = <<<'SOURCE'
<?php

Bar::TEST_CONSTANT
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\ClassConstFetch::class, $result->expr);
        $this->assertEquals('Bar', $result->expr->class->toString());
        $this->assertEquals('TEST_CONSTANT', $result->expr->name);
    }

    /**
     * @return void
     */
    public function testParsesStaticMethodCallsWithNamespacedClassNames(): void
    {
        $source = <<<'SOURCE'
<?php

NamespaceTest\Bar::staticmethod()
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\StaticCall::class, $result->expr);
        $this->assertEquals('NamespaceTest\Bar', $result->expr->class->toString());
        $this->assertEquals('staticmethod', $result->expr->name);
    }

    /**
     * @return void
     */
    public function testParsesPropertyFetches(): void
    {
        $source = <<<'SOURCE'
<?php

$this->someProperty
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\PropertyFetch::class, $result->expr);
        $this->assertEquals('this', $result->expr->var->name);
        $this->assertEquals('someProperty', $result->expr->name);
    }

    /**
     * @return void
     */
    public function testParsesStaticPropertyFetches(): void
    {
        $source = <<<'SOURCE'
<?php

self::$someProperty
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\StaticPropertyFetch::class, $result->expr);
        $this->assertEquals('self', $result->expr->class);
        $this->assertEquals('someProperty', $result->expr->name);
    }

    /**
     * @return void
     */
    public function testParsesStringWithDotsAndColons(): void
    {
        $source = <<<'SOURCE'
<?php

'.:'
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Scalar\String_::class, $result->expr);
        $this->assertEquals('.:', $result->expr->value);
    }

    /**
     * @return void
     */
    public function testParsesDynamicMethodCalls(): void
    {
        $source = <<<'SOURCE'
<?php

$this->{$foo}()->test()
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\MethodCall::class, $result->expr);
        $this->assertInstanceOf(Node\Expr\MethodCall::class, $result->expr->var);
        $this->assertInstanceOf(Node\Expr\Variable::class, $result->expr->var->var);
        $this->assertInstanceOf(Node\Expr\Variable::class, $result->expr->var->name);
        $this->assertEquals('this', $result->expr->var->var->name);
        $this->assertEquals('foo', $result->expr->var->name->name);
        $this->assertEquals('test', $result->expr->name);
    }

    /**
     * @return void
     */
    public function testParsesMemberAccessWithMissingMember(): void
    {
        $source = <<<'SOURCE'
<?php

$this->
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\PropertyFetch::class, $result->expr);
        $this->assertEquals('this', $result->expr->var->name);
        $this->assertEquals('', $result->expr->name);
    }

    /**
     * @return void
     */
    public function testParsesMethodCallOnInstantiationInParentheses(): void
    {
        $source = <<<'SOURCE'
<?php

(new Foo\Bar())->doFoo()
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\MethodCall::class, $result->expr);
        $this->assertInstanceOf(Node\Expr\New_::class, $result->expr->var);
        $this->assertEquals('Foo\Bar', $result->expr->var->class);
        $this->assertEquals('doFoo', $result->expr->name);
    }

    /**
     * @return void
     */
    public function testParsesMethodCallOnComplexCallStack(): void
    {
        $source = <<<'SOURCE'
<?php

$this
    ->testChaining(5, ['Somewhat more complex parameters', /* inline comment */ null])
    //------------
    /*
        another comment$this;[]{}**** /*int echo return
    */
    ->testChaining(2, [
    //------------
        'value1',
        'value2'
    ])

    ->testChaining(
    //------------
        3,
        [],
        function (FooClass $foo) {
            echo 'test';
            //    --------
            return $foo;
        }
    )

    ->testChaining(
    //------------
        nestedCall() - (2 * 5),
        nestedCall() - 3
    )

    ->testChai
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\PropertyFetch::class, $result->expr);
        $this->assertInstanceOf(Node\Expr\MethodCall::class, $result->expr->var);
        $this->assertInstanceOf(Node\Expr\MethodCall::class, $result->expr->var->var);
        $this->assertInstanceOf(Node\Expr\MethodCall::class, $result->expr->var->var->var);
        $this->assertInstanceOf(Node\Expr\MethodCall::class, $result->expr->var->var->var->var);
        $this->assertEquals('testChaining', $result->expr->var->name);
        $this->assertEquals('testChaining', $result->expr->var->var->name);
        $this->assertEquals('testChaining', $result->expr->var->var->var->name);
        $this->assertEquals('testChaining', $result->expr->var->var->var->var->name);
        $this->assertEquals('testChai', $result->expr->name);
    }

    /**
     * @return void
     */
    public function testParsesConstFetchOnStaticKeyword(): void
    {
        $source = <<<'SOURCE'
<?php

static::doSome
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\ClassConstFetch::class, $result->expr);
        $this->assertEquals('static', $result->expr->class);
        $this->assertEquals('doSome', $result->expr->name);
    }

    /**
     * @return void
     */
    public function testParsesEncapsedString(): void
    {
        $source = <<<'SOURCE'
<?php

"(($version{0} * 10000) + ($version{2} * 100) + $version{4}"
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Scalar\Encapsed::class, $result->expr);
        $this->assertInstanceOf(Node\Scalar\EncapsedStringPart::class, $result->expr->parts[0]);
        $this->assertEquals('((', $result->expr->parts[0]->value);
        $this->assertInstanceOf(Node\Expr\Variable::class, $result->expr->parts[1]);
        $this->assertEquals('version', $result->expr->parts[1]->name);
        $this->assertInstanceOf(Node\Scalar\EncapsedStringPart::class, $result->expr->parts[2]);
        $this->assertEquals('{0} * 10000) + (', $result->expr->parts[2]->value);
        $this->assertInstanceOf(Node\Expr\Variable::class, $result->expr->parts[3]);
        $this->assertEquals('version', $result->expr->parts[3]->name);
        $this->assertInstanceOf(Node\Scalar\EncapsedStringPart::class, $result->expr->parts[4]);
        $this->assertEquals('{2} * 100) + ', $result->expr->parts[4]->value);
        $this->assertInstanceOf(Node\Expr\Variable::class, $result->expr->parts[5]);
        $this->assertEquals('version', $result->expr->parts[5]->name);
        $this->assertInstanceOf(Node\Scalar\EncapsedStringPart::class, $result->expr->parts[6]);
        $this->assertEquals('{4}', $result->expr->parts[6]->value);
    }

    /**
     * @return void
     */
    public function testParsesEncapsedStringWithIntepolatedMethodCall(): void
    {
        $source = <<<'SOURCE'
<?php

"{$test->foo()}"
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Scalar\Encapsed::class, $result->expr);
        $this->assertInstanceOf(Node\Expr\MethodCall::class, $result->expr->parts[0]);
        $this->assertEquals('test', $result->expr->parts[0]->var->name);
        $this->assertEquals('foo', $result->expr->parts[0]->name);
    }

    /**
     * @return void
     */
    public function testParsesEncapsedStringWithIntepolatedPropertyFetch(): void
    {
        $source = <<<'SOURCE'
<?php

"{$test->foo}"
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Scalar\Encapsed::class, $result->expr);
        $this->assertInstanceOf(Node\Expr\PropertyFetch::class, $result->expr->parts[0]);
        $this->assertEquals('test', $result->expr->parts[0]->var->name);
        $this->assertEquals('foo', $result->expr->parts[0]->name);
    }

    /**
     * @return void
     */
    public function testParsesStringContainingIgnoredInterpolations(): void
    {
        $source = <<<'SOURCE'
<?php

'{$a->asd()[0]}'
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Scalar\String_::class, $result->expr);
        $this->assertEquals('{$a->asd()[0]}', $result->expr->value);
    }

    /**
     * @return void
     */
    public function testParsesNowdoc(): void
    {
        $source = <<<'SOURCE'
<?php

<<<'EOF'
TEST
EOF
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Scalar\String_::class, $result->expr);
        $this->assertEquals('TEST', $result->expr->value);
    }

    /**
     * @return void
     */
    public function testParsesHeredoc(): void
    {
        $source = <<<'SOURCE'
<?php

<<<EOF
TEST
EOF
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Scalar\String_::class, $result->expr);
        $this->assertEquals('TEST', $result->expr->value);
    }

    /**
     * @return void
     */
    public function testParsesHeredocContainingInterpolatedValues(): void
    {
        $source = <<<'SOURCE'
<?php

<<<EOF
EOF: {$foo[2]->bar()} some_text

This is / some text.

EOF
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Scalar\Encapsed::class, $result->expr);
        $this->assertInstanceOf(Node\Scalar\EncapsedStringPart::class, $result->expr->parts[0]);
        $this->assertEquals('EOF: ', $result->expr->parts[0]->value);
        $this->assertInstanceOf(Node\Expr\MethodCall::class, $result->expr->parts[1]);
        $this->assertInstanceOf(Node\Expr\ArrayDimFetch::class, $result->expr->parts[1]->var);
        $this->assertEquals('foo', $result->expr->parts[1]->var->var->name);
        $this->assertEquals(2, $result->expr->parts[1]->var->dim->value);
        $this->assertEquals('bar', $result->expr->parts[1]->name);
        $this->assertInstanceOf(Node\Scalar\EncapsedStringPart::class, $result->expr->parts[2]);
        $this->assertEquals(" some_text\n\nThis is / some text.\n", $result->expr->parts[2]->value);
    }

    /**
     * @return void
     */
    public function testParsesConstFetchWithSpecialClassConstantClassKeyword(): void
    {
        $source = <<<'SOURCE'
<?php

Test::class
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\ClassConstFetch::class, $result->expr);
        $this->assertEquals('Test', $result->expr->class->toString());
        $this->assertEquals('class', $result->expr->name);
    }

    /**
     * @return void
     */
    public function testParsesShiftExpression(): void
    {
        $source = <<<'SOURCE'
<?php

1 << 0
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\BinaryOp\ShiftLeft::class, $result->expr);
        $this->assertEquals(1, $result->expr->left->value);
        $this->assertEquals(0, $result->expr->right->value);
    }

    /**
     * @return void
     */
    public function testParsesExpressionWithBooleanNotOperator(): void
    {
        $source = <<<'SOURCE'
<?php

!$this->one
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\BooleanNot::class, $result->expr);
        $this->assertInstanceOf(Node\Expr\PropertyFetch::class, $result->expr->expr);
        $this->assertEquals('this', $result->expr->expr->var->name);
        $this->assertEquals('one', $result->expr->expr->name);
    }

    /**
     * @return void
     */
    public function testParsesExpressionWithSilencingOperator(): void
    {
        $source = <<<'SOURCE'
<?php

@$this->one
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\ErrorSuppress::class, $result->expr);
        $this->assertInstanceOf(Node\Expr\PropertyFetch::class, $result->expr->expr);
        $this->assertEquals('this', $result->expr->expr->var->name);
        $this->assertEquals('one', $result->expr->expr->name);
    }

    /**
     * @return void
     */
    public function testParsesExpressionWithTernaryOperatorWithMissingColon(): void
    {
        $source = <<<'SOURCE'
<?php

$test ? $a
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\Ternary::class, $result->expr);
        $this->assertInstanceOf(Node\Expr\Variable::class, $result->expr->cond);
        $this->assertEquals('test', $result->expr->cond->name);
        $this->assertInstanceOf(Node\Expr\Variable::class, $result->expr->if);
        $this->assertEquals('a', $result->expr->if->name);
        $this->assertInstanceOf(Expr\Dummy::class, $result->expr->else);
    }

    /**
     * @return void
     */
    public function testParsesExpressionWithTernaryOperatorWithMissingColonInAssignment(): void
    {
        $source = <<<'SOURCE'
<?php

$b = $test ? $a
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\Assign::class, $result->expr);
        $this->assertEquals('b', $result->expr->var->name);
        $this->assertInstanceOf(Node\Expr\Ternary::class, $result->expr->expr);
        $this->assertInstanceOf(Node\Expr\Variable::class, $result->expr->expr->cond);
        $this->assertEquals('test', $result->expr->expr->cond->name);
        $this->assertInstanceOf(Node\Expr\Variable::class, $result->expr->expr->if);
        $this->assertEquals('a', $result->expr->expr->if->name);
        $this->assertInstanceOf(Expr\Dummy::class, $result->expr->expr->else);
    }

    /**
     * @return void
     */
    public function testParsesFunctionCall(): void
    {
        $source = <<<'SOURCE'
<?php

call(1, 2
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\FuncCall::class, $result->expr);
        $this->assertEquals('call', $result->expr->name);
        $this->assertCount(2, $result->expr->args);
        $this->assertInstanceOf(Node\Arg::class, $result->expr->args[0]);
        $this->assertInstanceOf(Node\Scalar\LNumber::class, $result->expr->args[0]->value);
        $this->assertEquals(1, $result->expr->args[0]->value->value);
        $this->assertInstanceOf(Node\Arg::class, $result->expr->args[1]);
        $this->assertInstanceOf(Node\Scalar\LNumber::class, $result->expr->args[1]->value);
        $this->assertEquals(2, $result->expr->args[1]->value->value);
    }

    /**
     * @return void
     */
    public function testParsesFunctionCallWithMissingArgument(): void
    {
        $source = <<<'SOURCE'
<?php

call(1,
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\FuncCall::class, $result->expr);
        $this->assertEquals('call', $result->expr->name);
        $this->assertCount(1, $result->expr->args);
        $this->assertInstanceOf(Node\Arg::class, $result->expr->args[0]);
        $this->assertInstanceOf(Node\Scalar\LNumber::class, $result->expr->args[0]->value);
        $this->assertEquals(1, $result->expr->args[0]->value->value);
    }

    /**
     * @return void
     */
    public function testParsesMethodCall(): void
    {
        $source = <<<'SOURCE'
<?php

$this->call(1, 2
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\MethodCall::class, $result->expr);
        $this->assertEquals('call', $result->expr->name);
        $this->assertInstanceOf(Node\Expr\Variable::class, $result->expr->var);
        $this->assertEquals('this', $result->expr->var->name);
        $this->assertCount(2, $result->expr->args);
        $this->assertInstanceOf(Node\Arg::class, $result->expr->args[0]);
        $this->assertInstanceOf(Node\Scalar\LNumber::class, $result->expr->args[0]->value);
        $this->assertEquals(1, $result->expr->args[0]->value->value);
        $this->assertInstanceOf(Node\Arg::class, $result->expr->args[1]);
        $this->assertInstanceOf(Node\Scalar\LNumber::class, $result->expr->args[1]->value);
        $this->assertEquals(2, $result->expr->args[1]->value->value);
    }

    /**
     * @return void
     */
    public function testParsesMethodCallWithMissingArgument(): void
    {
        $source = <<<'SOURCE'
<?php

$this->call(1,
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\MethodCall::class, $result->expr);
        $this->assertEquals('call', $result->expr->name);
        $this->assertInstanceOf(Node\Expr\Variable::class, $result->expr->var);
        $this->assertEquals('this', $result->expr->var->name);
        $this->assertCount(1, $result->expr->args);
        $this->assertInstanceOf(Node\Arg::class, $result->expr->args[0]);
        $this->assertInstanceOf(Node\Scalar\LNumber::class, $result->expr->args[0]->value);
        $this->assertEquals(1, $result->expr->args[0]->value->value);
    }

    /**
     * @return void
     */
    public function testParsesStaticMethodCall(): void
    {
        $source = <<<'SOURCE'
<?php

self::call(1, 2
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\StaticCall::class, $result->expr);
        $this->assertEquals('call', $result->expr->name);
        $this->assertInstanceOf(Node\Name::class, $result->expr->class);
        $this->assertEquals('self', $result->expr->class->toString());
        $this->assertCount(2, $result->expr->args);
        $this->assertInstanceOf(Node\Arg::class, $result->expr->args[0]);
        $this->assertInstanceOf(Node\Scalar\LNumber::class, $result->expr->args[0]->value);
        $this->assertEquals(1, $result->expr->args[0]->value->value);
        $this->assertInstanceOf(Node\Arg::class, $result->expr->args[1]);
        $this->assertInstanceOf(Node\Scalar\LNumber::class, $result->expr->args[1]->value);
        $this->assertEquals(2, $result->expr->args[1]->value->value);
    }

    /**
     * @return void
     */
    public function testParsesStaticMethodCallWithMissingArgument(): void
    {
        $source = <<<'SOURCE'
<?php

self::call(1,
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\StaticCall::class, $result->expr);
        $this->assertEquals('call', $result->expr->name);
        $this->assertInstanceOf(Node\Name::class, $result->expr->class);
        $this->assertEquals('self', $result->expr->class->toString());
        $this->assertCount(1, $result->expr->args);
        $this->assertInstanceOf(Node\Arg::class, $result->expr->args[0]);
        $this->assertInstanceOf(Node\Scalar\LNumber::class, $result->expr->args[0]->value);
        $this->assertEquals(1, $result->expr->args[0]->value->value);
    }

    /**
     * @return void
     */
    public function testParsesConstructorCall(): void
    {
        $source = <<<'SOURCE'
<?php

new Foo(1, 2
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\New_::class, $result->expr);
        $this->assertInstanceOf(Node\Name::class, $result->expr->class);
        $this->assertEquals('Foo', $result->expr->class->toString());
        $this->assertCount(2, $result->expr->args);
        $this->assertInstanceOf(Node\Arg::class, $result->expr->args[0]);
        $this->assertInstanceOf(Node\Scalar\LNumber::class, $result->expr->args[0]->value);
        $this->assertEquals(1, $result->expr->args[0]->value->value);
        $this->assertInstanceOf(Node\Arg::class, $result->expr->args[1]);
        $this->assertInstanceOf(Node\Scalar\LNumber::class, $result->expr->args[1]->value);
        $this->assertEquals(2, $result->expr->args[1]->value->value);
    }

    /**
     * @return void
     */
    public function testParsesConstructorCallWithMissingArgument(): void
    {
        $source = <<<'SOURCE'
<?php

new Foo(1,
SOURCE;

        $result = $this->createPartialParser()->parse($source);

        $this->assertEquals(1, count($result));

        $result = array_shift($result);

        $this->assertInstanceOf(Node\Stmt\Expression::class, $result);
        $this->assertInstanceOf(Node\Expr\New_::class, $result->expr);
        $this->assertInstanceOf(Node\Name::class, $result->expr->class);
        $this->assertEquals('Foo', $result->expr->class->toString());
        $this->assertCount(1, $result->expr->args);
        $this->assertInstanceOf(Node\Arg::class, $result->expr->args[0]);
        $this->assertInstanceOf(Node\Scalar\LNumber::class, $result->expr->args[0]->value);
        $this->assertEquals(1, $result->expr->args[0]->value->value);
    }
}
