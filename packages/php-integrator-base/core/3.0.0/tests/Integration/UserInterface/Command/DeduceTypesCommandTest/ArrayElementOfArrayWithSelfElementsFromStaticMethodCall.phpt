<?php

namespace A;

class B
{
    /**
     * @return self[]
     */
    public static function bar()
    {

    }
}

$b = B::bar()[0];

// <MARKER>
