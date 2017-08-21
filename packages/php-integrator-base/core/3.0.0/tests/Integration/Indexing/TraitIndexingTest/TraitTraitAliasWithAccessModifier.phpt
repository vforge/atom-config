<?php

trait A
{
    public function foo() {  }
}

trait Test
{
    use A {
        foo as protected bar;
    }
}
