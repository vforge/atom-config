<?php

namespace PhpIntegrator\Parsing\Node\Keyword;

use PhpParser\Node\Expr;

/**
 * Represents the parent keyword.
 */
class Parent_ extends Expr
{
    /**
     * @inheritDoc
     */
    public function getSubNodeNames(): array
    {
        return [];
    }
}
