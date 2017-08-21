<?php

namespace A;

trait T {}
trait T2 {}

class TestClass
{
    use T, T;
    use T2;
    use T2;
}
