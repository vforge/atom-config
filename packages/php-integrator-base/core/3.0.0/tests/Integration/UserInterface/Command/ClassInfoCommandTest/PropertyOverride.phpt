<?php

namespace A;

class AncestorClass
{
    protected $ancestorProperty;
}

trait ParentTrait
{
    protected $parentTraitProperty;
}

class ParentClass extends AncestorClass
{
    use ParentTrait;

    protected $parentProperty;
    protected $ancestorProperty;
}

class ChildClass extends ParentClass
{
    use TestTrait;

    protected $parentTraitProperty;
    protected $parentProperty;
    protected $ancestorProperty;
}
