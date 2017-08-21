<?php

namespace A;

/**
 * This is a summary.
 */
trait B
{
    public function foo() {}
}

class C
{
    use B {
        B::foo as bar;
    }
}
