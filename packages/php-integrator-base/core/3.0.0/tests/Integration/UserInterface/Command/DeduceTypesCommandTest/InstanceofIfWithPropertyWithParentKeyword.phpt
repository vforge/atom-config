<?php

namespace A;

class ParentClass
{
    public $foo;
}

class Test extends ParentClass
{
    public function foo()
    {
        if (parent::$foo instanceof B) {
            // <MARKER>
        }
    }
}
