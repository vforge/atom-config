<?php

namespace PhpIntegrator\Linting;

use PhpIntegrator\NameQualificationUtilities\ConstantPresenceIndicatorInterface;

/**
 * Factory that produces instances of {@see UnknownGlobalConstantAnalyzer}.
 */
class UnknownGlobalConstantAnalyzerFactory
{
    /**
     * @var ConstantPresenceIndicatorInterface
     */
    private $constantPresenceIndicatorInterface;

    /**
     * @param ConstantPresenceIndicatorInterface $constantPresenceIndicatorInterface
     */
    public function __construct(ConstantPresenceIndicatorInterface $constantPresenceIndicatorInterface)
    {
        $this->constantPresenceIndicatorInterface = $constantPresenceIndicatorInterface;
    }

    /**
     * @return UnknownGlobalConstantAnalyzer
     */
    public function create(): UnknownGlobalConstantAnalyzer
    {
        return new UnknownGlobalConstantAnalyzer(
            $this->constantPresenceIndicatorInterface
        );
    }
}
