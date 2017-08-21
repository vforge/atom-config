<?php

namespace A;

class Bar
{
    public function __construct(int $b)
    {
        // TODO
    }
}

class Foo
{
    /**
     * @param int $a
     */
    public function __construct($a)
    {
        $test = new Foo( new Bar( 1 ) );
    }
}
