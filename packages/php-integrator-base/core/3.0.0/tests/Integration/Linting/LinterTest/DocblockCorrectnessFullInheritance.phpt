<?php

namespace A;

class Foo {}

/**
 * {@inheritDoc}
 */
function some_function_correct_1($param1, Foo $param2)
{

}

/**
 * {@inheritdoc}
 */
function some_function_correct_2($param1, Foo $param2)
{

}

/**
 * @inheritDoc
 */
function some_function_correct_3($param1, Foo $param2)
{

}
