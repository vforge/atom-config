<?php

namespace A;

trait T
{
    protected function test()
    {

    }
}

class TestClass
{
    use T {
        test as test1;
    }
}
