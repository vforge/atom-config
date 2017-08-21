<?php

namespace A;

class B
{
    /**
     * @var B|null
     */
    public $foo;
}

$b = new B();

if ($b->foo &&
    // <MARKER>
    true
) {

}
