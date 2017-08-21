<?php

namespace A;

trait TestTrait
{
    protected function someMethod()
    {

    }
}

class TestClass
{
    use TestTrait;

    protected function someMethod()
    {

    }
}
