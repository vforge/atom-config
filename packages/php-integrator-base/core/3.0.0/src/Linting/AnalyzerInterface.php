<?php

namespace PhpIntegrator\Linting;

/**
 * Interface for analyzers.
 */
interface AnalyzerInterface
{
    /**
     * Retrieves a list of visitors to attach.
     *
     * @return \PhpParser\NodeVisitor[]
     */
    public function getVisitors(): array;

    /**
     * Retrieves a list of errors found during traversal.
     *
     * This method will only produce the correct output after visiting has occurred.
     *
     * @return array
     */
    public function getErrors(): array;

    /**
     * Retrieves a list of warnings found during traversal.
     *
     * This method will only produce the correct output after visiting has occurred.
     *
     * @return array
     */
    public function getWarnings(): array;
}
