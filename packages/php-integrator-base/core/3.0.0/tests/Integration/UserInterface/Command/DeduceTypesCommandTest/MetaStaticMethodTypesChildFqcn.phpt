<?php

namespace A;

class Foo
{

}

class FooChild extends Foo
{
    public function get(string $test)
    {

    }
}

$fooChild = new FooChild();
$var = $fooChild->get('bar');
// <MARKER>
