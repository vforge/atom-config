<?php

namespace A;

class Foo
{
    /**
     * Some summary.
     *
     * @param int    $a    Parameter A.
     * @param bool[] ...$b
     */
    public static function test($a, ...$b)
    {
        static::test(1, 2, 3);
    }
}
