<?php

namespace A;

trait FirstTrait
{
    protected function someMethod()
    {

    }
}

trait TestTrait
{
    use FirstTrait;

    protected function someMethod()
    {

    }
}
