<?php

namespace A;

trait TestTrait
{
    public function someMethod()
    {

    }
}

interface TestInterface
{
    public function someMethod();
}

class TestClass implements TestInterface
{
    use TestTrait;
}
