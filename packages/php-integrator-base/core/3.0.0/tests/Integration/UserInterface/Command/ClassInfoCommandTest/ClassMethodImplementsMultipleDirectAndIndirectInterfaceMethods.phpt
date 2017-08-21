<?php

namespace A;

interface TestInterface1
{
    public function someMethod();
}

interface TestInterface2
{
    public function someMethod();
}

class BaseClass extends TestInterface1
{

}

class TestClass extends BaseClass implements TestInterface2
{
    public function someMethod()
    {

    }
}
