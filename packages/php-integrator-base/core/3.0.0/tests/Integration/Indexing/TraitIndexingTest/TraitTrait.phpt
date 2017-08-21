<?php

trait A {}
trait B {}

trait Test
{
    use A, B;
}
