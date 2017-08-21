<?php

namespace A;

interface TestInterface
{
    public function interfaceMethod();
}

class ParentClass
{
    public function interfaceMethod()
    {
        
    }
}

class ChildClass extends ParentClass implements TestInterface
{
    public function interfaceMethod()
    {
        // Override and implementation
    }
}
