<?php

namespace A;

class Foo {}
class Bar {}
class Baz {}

/**
 * à couple of Unicode chàràcters
 *
 * @param mixed    $param1
 * @param Foo      $param2
 * @param Bar      $param3
 * @param int[]    $param4
 */
function some_function_correct($param1, Foo $param2, Bar $param3, array $param4)
{

}

/**
 * @param Baz $param1
 */
function some_function_parameter_incorrect_type(Foo $param1)
{

}
