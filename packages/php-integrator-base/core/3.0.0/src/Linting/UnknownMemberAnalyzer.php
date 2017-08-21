<?php

namespace PhpIntegrator\Linting;

use PhpIntegrator\Analysis\ClasslikeInfoBuilder;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

use PhpIntegrator\Analysis\Typing\Deduction\NodeTypeDeducerInterface;

use PhpIntegrator\Analysis\Visiting\MemberUsageFetchingVisitor;

use PhpIntegrator\Indexing\Structures;

/**
 * Looks for unknown member names.
 */
class UnknownMemberAnalyzer implements AnalyzerInterface
{
    /**
     * @var MemberUsageFetchingVisitor
     */
    private $methodUsageFetchingVisitor;

    /**
     * @param NodeTypeDeducerInterface $nodeTypeDeducer
     * @param ClasslikeInfoBuilder     $classlikeInfoBuilder
     * @param TypeAnalyzer             $typeAnalyzer
     * @param Structures\File          $file
     * @param string                   $code
     */
    public function __construct(
        NodeTypeDeducerInterface $nodeTypeDeducer,
        ClasslikeInfoBuilder $classlikeInfoBuilder,
        TypeAnalyzer $typeAnalyzer,
        Structures\File $file,
        string $code
    ) {
        $this->methodUsageFetchingVisitor = new MemberUsageFetchingVisitor(
            $nodeTypeDeducer,
            $classlikeInfoBuilder,
            $typeAnalyzer,
            $file,
            $code
        );
    }

    /**
     * @inheritDoc
     */
    public function getVisitors(): array
    {
        return [
            $this->methodUsageFetchingVisitor
        ];
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        $output = [];

        $memberCallList = $this->methodUsageFetchingVisitor->getMemberCallList();

        foreach ($memberCallList as $memberCall) {
            $message = null;
            $type = $memberCall['type'];

            if ($type === MemberUsageFetchingVisitor::TYPE_EXPRESSION_HAS_NO_TYPE) {
                $message = "Member ‘{$memberCall['memberName']}’ could not be found because expression has no type.";
            } elseif ($type === MemberUsageFetchingVisitor::TYPE_EXPRESSION_HAS_NO_SUCH_MEMBER) {
                $message = "Member ‘{$memberCall['memberName']}’ does not exist for type ‘{$memberCall['expressionType']}’.";
            } else {
                continue;
            }

            $output[] = [
                'message' => $message,
                'start'   => $memberCall['start'],
                'end'     => $memberCall['end']
            ];
        }

        return $output;
    }

    /**
     * @inheritDoc
     */
    public function getWarnings(): array
    {
        $output = [];

        $memberCallList = $this->methodUsageFetchingVisitor->getMemberCallList();

        foreach ($memberCallList as $memberCall) {
            $type = $memberCall['type'];

            unset ($memberCall['type']);

            if ($type === MemberUsageFetchingVisitor::TYPE_EXPRESSION_NEW_MEMBER_WILL_BE_CREATED) {
                $output[] = [
                    'message' => "Member ‘{$memberCall['memberName']}’ was not explicitly defined in ‘{$memberCall['expressionType']}’. It will be created at runtime.",
                    'start'   => $memberCall['start'],
                    'end'     => $memberCall['end']
                ];
            }
        }

        return $output;
    }
}
