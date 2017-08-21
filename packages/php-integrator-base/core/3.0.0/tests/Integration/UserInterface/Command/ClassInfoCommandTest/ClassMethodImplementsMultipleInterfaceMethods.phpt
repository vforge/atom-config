<?php

namespace A;

interface TestInterface1
{
    public function someMethod();
}

interface TestInterface2
{
    public function someMethod();
}

class TestClass implements TestInterface1, TestInterface2
{
    public function someMethod()
    {

    }
}
