<?php

namespace A;

interface TestInterface
{
    public function interfaceMethod();
}

class ParentClass implements TestInterface
{
    public function interfaceMethod()
    {
        // Implementation
    }
}

class ChildClass extends ParentClass
{
    public function interfaceMethod()
    {
        // Override of implementation
    }
}
