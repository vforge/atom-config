<?php

namespace A;

trait TestTrait
{
    public function someMethod()
    {

    }
}

class BaseClass
{
    public function someMethod()
    {

    }
}

class TestClass extends BaseClass
{
    use TestTrait;
}
