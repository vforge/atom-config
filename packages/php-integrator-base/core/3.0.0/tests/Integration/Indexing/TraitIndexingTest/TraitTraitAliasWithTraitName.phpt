<?php

trait A
{
    public function foo() {  }
}

trait Test
{
    use A {
        A::foo as bar;
    }
}
