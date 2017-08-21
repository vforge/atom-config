<?php

namespace PhpIntegrator\Analysis\Visiting;

/**
 * Kinds of use statements.
 */
class UseStatementKind
{
    /**
     * @var string
     */
    public const TYPE_CLASSLIKE = 'classlike';

    /**
     * @var string
     */
    public const TYPE_FUNCTION = 'function';

    /**
     * @var string
     */
    public const TYPE_CONSTANT = 'constant';
}
