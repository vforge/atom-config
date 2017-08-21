<?php

trait A
{
    public function foo() {  }
}

trait B
{
    public function foo() {  }
}

class Test
{
    use A, B {
        A::foo insteadof foo;
    }
}
