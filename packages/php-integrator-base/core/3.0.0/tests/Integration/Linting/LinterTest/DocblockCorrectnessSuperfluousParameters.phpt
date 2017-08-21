<?php

namespace A;

class Foo {}

/**
 * @param mixed     $param1
 * @param Foo $param2
 */
function some_function_correct($param1, Foo $param2)
{

}

/**
 * @param mixed $param1
 * @param Foo   $param2
 * @param mixed $extra1
 * @param mixed $extra2
 */
function some_function_extra_parameter($param1, Foo $param2)
{

}
