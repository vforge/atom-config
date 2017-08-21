<?php

namespace A;

interface BaseInterface
{
    public function interfaceMethod();
}

interface TestInterface extends BaseInterface
{
    public function interfaceMethod();
}
