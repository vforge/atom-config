<?php

namespace A;

class Test
{
    protected $foo;

    public function foo()
    {
        if ($this->foo instanceof B) {
            // <MARKER>
        }
    }
}
