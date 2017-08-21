<?php

namespace PhpIntegrator\Linting;

use PhpIntegrator\NameQualificationUtilities\FunctionPresenceIndicatorInterface;

/**
 * Factory that produces instances of {@see UnknownGlobalFunctionAnalyzer}.
 */
class UnknownGlobalFunctionAnalyzerFactory
{
    /**
     * @var FunctionPresenceIndicatorInterface
     */
    private $functionPresenceIndicatorInterface;

    /**
     * @param FunctionPresenceIndicatorInterface $functionPresenceIndicatorInterface
     */
    public function __construct(FunctionPresenceIndicatorInterface $functionPresenceIndicatorInterface)
    {
        $this->functionPresenceIndicatorInterface = $functionPresenceIndicatorInterface;
    }

    /**
     * @return UnknownGlobalFunctionAnalyzer
     */
    public function create(): UnknownGlobalFunctionAnalyzer
    {
        return new UnknownGlobalFunctionAnalyzer(
            $this->functionPresenceIndicatorInterface
        );
    }
}
