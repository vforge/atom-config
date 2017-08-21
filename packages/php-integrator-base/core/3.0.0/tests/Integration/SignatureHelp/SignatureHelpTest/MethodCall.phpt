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
    public function test($a, $b = true, $c)
    {
        $this->test(1, 2, 3);
    }
}
