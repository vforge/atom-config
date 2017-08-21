<?php

namespace A;

/**
 * This is a summary.
 */
trait B
{
    public function foo() {}
}

trait D
{
    public function foo() {}
}

class C
{
    use B {
        B::foo insteadof foo;
    }
}
