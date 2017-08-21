<?php

namespace A;

trait TestTrait
{
    protected function someMethod()
    {

    }
}

class BaseClass
{
    protected function someMethod()
    {

    }
}

class TestClass extends BaseClass
{
    use TestTrait;

    protected function someMethod()
    {

    }
}
