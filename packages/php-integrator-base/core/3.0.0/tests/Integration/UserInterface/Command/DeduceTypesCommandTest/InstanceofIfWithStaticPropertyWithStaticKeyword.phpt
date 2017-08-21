<?php

namespace A;

class Test
{
    public static $foo;

    public function foo()
    {
        if (static::$foo instanceof B) {
            // <MARKER>
        }
    }
}
