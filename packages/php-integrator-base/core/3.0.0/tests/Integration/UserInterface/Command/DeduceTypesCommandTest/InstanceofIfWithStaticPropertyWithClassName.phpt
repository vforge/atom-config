<?php

namespace A;

class Test
{
    public static $foo;

    public function foo()
    {
        if (Test::$foo instanceof B) {
            // <MARKER>
        }
    }
}
