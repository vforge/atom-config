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
    public static function test($a, $b = true, $c)
    {
        static::test(1, 2, 3);
    }
}
