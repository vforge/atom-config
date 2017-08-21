<?php

trait A
{
    public function foo() {  }
}

class Test
{
    use A {
        A::foo as bar;
    }
}
