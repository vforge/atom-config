<?php

namespace A;

class Foo
{
    /**
     * Some summary.
     *
     * @param int    $a Parameter A.
     * @param bool   $b
     * @param string $c Parameter C.
     */
    public function __construct($a, $b = true, $c)
    {
        $test = new Foo(
            1,
            2,
            3
        );
    }
}
