<?php

namespace A;

class Test
{
    public static $foo;

    public function foo()
    {
        if (self::$foo instanceof B) {
            // <MARKER>
        }
    }
}
