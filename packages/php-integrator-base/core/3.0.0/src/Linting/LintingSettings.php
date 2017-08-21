<?php

namespace PhpIntegrator\Linting;

/**
 * Describes settings to pass to a lint operation.
 *
 * This is a value object and immutable.
 */
class LintingSettings
{
    /**
     * @var bool
     */
    private $lintUnknownClasses;

    /**
     * @var bool
     */
    private $lintUnknownMembers;

    /**
     * @var bool
     */
    private $lintUnknownGlobalFunctions;

    /**
     * @var bool
     */
    private $lintUnknownGlobalConstants;

    /**
     * @var bool
     */
    private $lintDocblockCorrectness;

    /**
     * @var bool
     */
    private $lintUnusedUseStatements;

    /**
     * @var bool
     */
    private $lintMissingDocumentation;

    /**
     * @param bool $lintUnknownClasses
     * @param bool $lintUnknownMembers
     * @param bool $lintUnknownGlobalFunctions
     * @param bool $lintUnknownGlobalConstants
     * @param bool $lintDocblockCorrectness
     * @param bool $lintUnusedUseStatements
     * @param bool $lintMissingDocumentation
     */
    public function __construct(
        bool $lintUnknownClasses,
        bool $lintUnknownMembers,
        bool $lintUnknownGlobalFunctions,
        bool $lintUnknownGlobalConstants,
        bool $lintDocblockCorrectness,
        bool $lintUnusedUseStatements,
        bool $lintMissingDocumentation
    ) {
        $this->lintUnknownClasses = $lintUnknownClasses;
        $this->lintUnknownMembers = $lintUnknownMembers;
        $this->lintUnknownGlobalFunctions = $lintUnknownGlobalFunctions;
        $this->lintUnknownGlobalConstants = $lintUnknownGlobalConstants;
        $this->lintDocblockCorrectness = $lintDocblockCorrectness;
        $this->lintUnusedUseStatements = $lintUnusedUseStatements;
        $this->lintMissingDocumentation = $lintMissingDocumentation;
    }

    /**
     * @return bool
     */
    public function getLintUnknownClasses(): bool
    {
        return $this->lintUnknownClasses;
    }

    /**
     * @return bool
     */
    public function getLintUnknownMembers(): bool
    {
        return $this->lintUnknownMembers;
    }

    /**
     * @return bool
     */
    public function getLintUnknownGlobalFunctions(): bool
    {
        return $this->lintUnknownGlobalFunctions;
    }

    /**
     * @return bool
     */
    public function getLintUnknownGlobalConstants(): bool
    {
        return $this->lintUnknownGlobalConstants;
    }

    /**
     * @return bool
     */
    public function getLintDocblockCorrectness(): bool
    {
        return $this->lintDocblockCorrectness;
    }

    /**
     * @return bool
     */
    public function getLintUnusedUseStatements(): bool
    {
        return $this->lintUnusedUseStatements;
    }

    /**
     * @return bool
     */
    public function getLintMissingDocumentation(): bool
    {
        return $this->lintMissingDocumentation;
    }
}
