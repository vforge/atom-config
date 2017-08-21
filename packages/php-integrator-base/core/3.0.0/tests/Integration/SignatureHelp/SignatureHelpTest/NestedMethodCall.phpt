<?php

namespace A;

class B
{
    /**
     * Some summary.
     *
     * @param int $a Parameter A.
     */
    public function foo($a)
    {

    }

    public function bar()
    {
        $b = new B();
        $b->foo( $this->bar(  ) );
    }
}
