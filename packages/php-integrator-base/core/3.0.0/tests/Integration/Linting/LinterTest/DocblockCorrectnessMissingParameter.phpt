<?php

namespace A;

class Foo {}

/**
 * @param mixed $param1
 * @param Foo   $param2
 */
function some_function_correct($param1, Foo $param2)
{

}

/**
 * @param mixed $param1
 */
function some_function_missing_parameter($param1, Foo $param2)
{

}
