<?php

namespace A;

interface ParentInterface
{
    public function interfaceParentMethod();
}

interface ChildInterface extends ParentInterface
{

}

class ChildClass implements ChildInterface
{
    public function interfaceParentMethod()
    {

    }
}
