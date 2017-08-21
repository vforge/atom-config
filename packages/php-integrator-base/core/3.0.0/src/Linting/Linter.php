<?php

namespace PhpIntegrator\Linting;

use PhpIntegrator\Indexing\Structures;

use PhpIntegrator\Utility\SourceCodeHelpers;

use PhpParser\Error;
use PhpParser\Parser;
use PhpParser\ErrorHandler;
use PhpParser\NodeTraverser;

/**
 * Lints a file syntactically as well as semantically to indicate various problems with its contents.
 */
class Linter
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var DocblockCorrectnessAnalyzerFactory
     */
    private $docblockCorrectnessAnalyzerFactory;

    /**
     * @var UnknownClassAnalyzerFactory
     */
    private $unknownClassAnalyzerFactory;

    /**
     * @var UnknownGlobalConstantAnalyzerFactory
     */
    private $unknownGlobalConstantAnalyzerFactory;

    /**
     * @var UnknownGlobalFunctionAnalyzerFactory
     */
    private $unknownGlobalFunctionAnalyzerFactory;

    /**
     * @var UnknownMemberAnalyzerFactory
     */
    private $unknownMemberAnalyzerFactory;

    /**
     * @var UnusedUseStatementAnalyzerFactory
     */
    private $unusedUseStatementAnalyzerFactory;

    /**
     * @var DocblockMissingAnalyzerFactory
     */
    private $docblockMissingAnalyzerFactory;

    /**
     * @param Parser                               $parser
     * @param DocblockCorrectnessAnalyzerFactory   $docblockCorrectnessAnalyzerFactory
     * @param UnknownClassAnalyzerFactory          $unknownClassAnalyzerFactory
     * @param UnknownGlobalConstantAnalyzerFactory $unknownGlobalConstantAnalyzerFactory
     * @param UnknownGlobalFunctionAnalyzerFactory $unknownGlobalFunctionAnalyzerFactory
     * @param UnknownMemberAnalyzerFactory         $unknownMemberAnalyzerFactory
     * @param UnusedUseStatementAnalyzerFactory    $unusedUseStatementAnalyzerFactory
     * @param DocblockMissingAnalyzerFactory       $docblockMissingAnalyzerFactory
     */
    public function __construct(
        Parser $parser,
        DocblockCorrectnessAnalyzerFactory $docblockCorrectnessAnalyzerFactory,
        UnknownClassAnalyzerFactory $unknownClassAnalyzerFactory,
        UnknownGlobalConstantAnalyzerFactory $unknownGlobalConstantAnalyzerFactory,
        UnknownGlobalFunctionAnalyzerFactory $unknownGlobalFunctionAnalyzerFactory,
        UnknownMemberAnalyzerFactory $unknownMemberAnalyzerFactory,
        UnusedUseStatementAnalyzerFactory $unusedUseStatementAnalyzerFactory,
        DocblockMissingAnalyzerFactory $docblockMissingAnalyzerFactory
    ) {
        $this->parser = $parser;
        $this->docblockCorrectnessAnalyzerFactory = $docblockCorrectnessAnalyzerFactory;
        $this->unknownClassAnalyzerFactory = $unknownClassAnalyzerFactory;
        $this->unknownGlobalConstantAnalyzerFactory = $unknownGlobalConstantAnalyzerFactory;
        $this->unknownGlobalFunctionAnalyzerFactory = $unknownGlobalFunctionAnalyzerFactory;
        $this->unknownMemberAnalyzerFactory = $unknownMemberAnalyzerFactory;
        $this->unusedUseStatementAnalyzerFactory = $unusedUseStatementAnalyzerFactory;
        $this->docblockMissingAnalyzerFactory = $docblockMissingAnalyzerFactory;
    }

    /**
     * @param Structures\File $file
     * @param string          $code
     * @param LintingSettings $settings
     *
     * @return array
     */
    public function lint(Structures\File $file, string $code, LintingSettings $settings): array
    {
        // Parse the file to fetch the information we need.
        $nodes = [];
        $parser = $this->parser;

        $handler = new ErrorHandler\Collecting();

        $nodes = $parser->parse($code, $handler);

        $output = [
            'errors'   => [],
            'warnings' => []
        ];

        foreach ($handler->getErrors() as $e) {
            $startLine = $e->getStartLine() >= 0 ? ($e->getStartLine() - 1) : 0;
            $endLine   = $e->getEndLine() >= 0 ? ($e->getEndLine() - 1) : 0;

            $startColumn = $e->hasColumnInfo() ? ($e->getStartColumn($code) - 1) : 0;
            $endColumn   = $e->hasColumnInfo() ? ($e->getEndColumn($code) - 1) : 0;

            $output['errors'][] = [
                'message'     => $e->getMessage(),
                'start'       => SourceCodeHelpers::calculateOffsetByLineCharacter($code, $startLine, $startColumn),
                'end'         => SourceCodeHelpers::calculateOffsetByLineCharacter($code, $endLine, $endColumn)
            ];
        }

        if ($nodes === null) {
            return $output;
        }

        $traverser = new NodeTraverser();
        $analyzers = $this->getAnalyzersForRequest($file, $code, $settings);

        foreach ($analyzers as $analyzer) {
            foreach ($analyzer->getVisitors() as $visitor) {
                $traverser->addVisitor($visitor);
            }
        }

        try {
            $traverser->traverse($nodes);
        } catch (Error $e) {
            $output['errors'][] = [
                'message' => "Something is semantically wrong. Is there perhaps a duplicate use statement?",
                'start'   => 0,
                'end'     => 0
            ];

            return $output;
        }

        foreach ($analyzers as $analyzer) {
            $output['errors']   = array_merge($output['errors'], $analyzer->getErrors());
            $output['warnings'] = array_merge($output['warnings'], $analyzer->getWarnings());
        }

        return $output;
    }

    /**
     * @param Structures\File $file
     * @param string          $code
     * @param LintingSettings $settings
     *
     * @return AnalyzerInterface[]
     */
    protected function getAnalyzersForRequest(Structures\File $file, string $code, LintingSettings $settings): array
    {
        /** @var AnalyzerInterface[] $analyzers */
        $analyzers = [];

        if ($settings->getLintUnknownClasses()) {
            $analyzers[] = $this->unknownClassAnalyzerFactory->create($file->getPath());
        }

        if ($settings->getLintUnknownMembers()) {
            $analyzers[] = $this->unknownMemberAnalyzerFactory->create($file, $code);
        }

        if ($settings->getLintUnusedUseStatements()) {
            $analyzers[] = $this->unusedUseStatementAnalyzerFactory->create($code);
        }

        if ($settings->getLintDocblockCorrectness()) {
            $analyzers[] = $this->docblockCorrectnessAnalyzerFactory->create($file->getPath(), $code);
        }

        if ($settings->getLintUnknownGlobalConstants()) {
            $analyzers[] = $this->unknownGlobalConstantAnalyzerFactory->create();
        }

        if ($settings->getLintUnknownGlobalFunctions()) {
            $analyzers[] = $this->unknownGlobalFunctionAnalyzerFactory->create();
        }

        if ($settings->getLintMissingDocumentation()) {
            $analyzers[] = $this->docblockMissingAnalyzerFactory->create($code);
        }

        return $analyzers;
    }
}
