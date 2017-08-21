<?php

namespace PhpIntegrator\Parsing\Node\Keyword;

use PhpParser\Node\Expr;

/**
 * Represents the self keyword.
 */
class Self_ extends Expr
{
    /**
     * @inheritDoc
     */
    public function getSubNodeNames(): array
    {
        return [];
    }
}
