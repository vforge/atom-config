<?php

trait A
{
    public function foo() {  }
}

trait B
{
    public function foo() {  }
}

trait Test
{
    use A, B {
        A::foo insteadof foo;
    }
}
