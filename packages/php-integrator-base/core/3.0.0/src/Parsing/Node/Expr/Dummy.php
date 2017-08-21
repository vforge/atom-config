<?php

namespace PhpIntegrator\Parsing\Node\Expr;

use PhpParser\Node;

/**
 * Dummy expression that can be inserted in locations were an expression node is expected to be present, but it should
 * not actually contain anything useful.
 */
class Dummy extends Node\Expr
{
    /**
     * @inheritDoc
     */
    public function getSubNodeNames(): array
    {
        return [];
    }
}
