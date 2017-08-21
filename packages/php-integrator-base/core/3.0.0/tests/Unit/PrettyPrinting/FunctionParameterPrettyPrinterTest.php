<?php

namespace PhpIntegrator\Tests\Unit\PrettyPrinting;

use PhpIntegrator\PrettyPrinting\TypePrettyPrinter;
use PhpIntegrator\PrettyPrinting\TypeListPrettyPrinter;
use PhpIntegrator\PrettyPrinting\ParameterNamePrettyPrinter;
use PhpIntegrator\PrettyPrinting\FunctionParameterPrettyPrinter;
use PhpIntegrator\PrettyPrinting\ParameterDefaultValuePrettyPrinter;

class FunctionParameterPrettyPrinterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return FunctionParameterPrettyPrinter
     */
    protected function getFunctionParameterPrettyPrinterStub(): FunctionParameterPrettyPrinter
    {
        return new FunctionParameterPrettyPrinter(
            new ParameterDefaultValuePrettyPrinter(),
            new TypeListPrettyPrinter(
                new TypePrettyPrinter()
            ),
            new ParameterNamePrettyPrinter()
        );
    }

    /**
     * @return void
     */
    public function testName(): void
    {
        $result = $this->getFunctionParameterPrettyPrinterStub()->print([
            'name'         => 'test',
            'isVariadic'   => false,
            'isReference'  => false,
            'defaultValue' => null,
            'types'        => []
        ]);

        $this->assertEquals('$test', $result);
    }

    /**
     * @return void
     */
    public function testReference(): void
    {
        $result = $this->getFunctionParameterPrettyPrinterStub()->print([
            'name'         => 'test',
            'isVariadic'   => false,
            'isReference'  => true,
            'defaultValue' => null,
            'types'        => []
        ]);

        $this->assertEquals('&$test', $result);
    }

    /**
     * @return void
     */
    public function testVariadic(): void
    {
        $result = $this->getFunctionParameterPrettyPrinterStub()->print([
            'name'         => 'test',
            'isVariadic'   => true,
            'isReference'  => false,
            'defaultValue' => null,
            'types'        => []
        ]);

        $this->assertEquals('...$test', $result);
    }

    /**
     * @return void
     */
    public function testSingleType(): void
    {
        $result = $this->getFunctionParameterPrettyPrinterStub()->print([
            'name'         => 'test',
            'isVariadic'   => false,
            'isReference'  => false,
            'defaultValue' => null,

            'types' => [
                [
                    'type' => 'int'
                ]
            ]
        ]);

        $this->assertEquals('int $test', $result);
    }

    /**
     * @return void
     */
    public function testMultipleTypes(): void
    {
        $result = $this->getFunctionParameterPrettyPrinterStub()->print([
            'name'         => 'test',
            'isVariadic'   => false,
            'isReference'  => false,
            'defaultValue' => null,

            'types' => [
                [
                    'type' => 'int'
                ],

                [
                    'type' => 'bool'
                ]
            ]
        ]);

        $this->assertEquals('int|bool $test', $result);
    }

    /**
     * @return void
     */
    public function testDefaultValue(): void
    {
        $result = $this->getFunctionParameterPrettyPrinterStub()->print([
            'name'         => 'test',
            'isVariadic'   => false,
            'isReference'  => false,
            'defaultValue' => 'null',
            'types'        => []
        ]);

        $this->assertEquals('$test = null', $result);
    }

    /**
     * @return void
     */
    public function testDefaultValueOfIntegerZero(): void
    {
        $result = $this->getFunctionParameterPrettyPrinterStub()->print([
            'name'         => 'test',
            'isVariadic'   => false,
            'isReference'  => false,
            'defaultValue' => 0,
            'types'        => []
        ]);

        $this->assertEquals('$test = 0', $result);
    }
}
