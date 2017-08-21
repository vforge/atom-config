<?php

namespace A;

trait T
{
    protected function test()
    {

    }
}

trait TestTrait
{
    use T {
        test as private test1;
    }
}
