<?php

trait A
{
    public function foo() {  }
}

class Test
{
    use A {
        foo as protected bar;
    }
}
