<?php

namespace A;

interface ParentInterface
{
    public function parentInterfaceMethod();
}

abstract class ParentClass implements ParentInterface
{

}

class ChildClass extends ParentClass
{
    public function parentInterfaceMethod(Foo $foo = null)
    {

    }
}
