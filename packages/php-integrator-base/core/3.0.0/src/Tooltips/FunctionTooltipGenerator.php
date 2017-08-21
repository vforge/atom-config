<?php

namespace PhpIntegrator\Tooltips;

use PhpIntegrator\PrettyPrinting\ParameterNamePrettyPrinter;

/**
 * Generates tooltips for functions.
 */
class FunctionTooltipGenerator
{
    /**
     * @var ParameterNamePrettyPrinter
     */
    private $parameterNamePrettyPrinter;

    /**
     * @var TooltipTypeListPrettyPrinter
     */
    private $tooltipTypeListPrettyPrinter;

    /**
     * @param ParameterNamePrettyPrinter   $parameterNamePrettyPrinter
     * @param TooltipTypeListPrettyPrinter $tooltipTypeListPrettyPrinter
     */
    public function __construct(
        ParameterNamePrettyPrinter $parameterNamePrettyPrinter,
        TooltipTypeListPrettyPrinter $tooltipTypeListPrettyPrinter
    ) {
        $this->parameterNamePrettyPrinter = $parameterNamePrettyPrinter;
        $this->tooltipTypeListPrettyPrinter = $tooltipTypeListPrettyPrinter;
    }

    /**
     * @param array $functionInfo
     *
     * @return string
     */
    public function generate(array $functionInfo): string
    {
        $sections = [
            $this->generateSummary($functionInfo),
            $this->generateLongDescription($functionInfo),
            $this->generateParameters($functionInfo),
            $this->generateReturn($functionInfo),
            $this->generateThrows($functionInfo)
        ];

        return implode("\n\n", array_filter($sections));
    }

    /**
     * @param array $functionInfo
     *
     * @return string
     */
    protected function generateSummary(array $functionInfo): string
    {
        if ($functionInfo['shortDescription']) {
            return $functionInfo['shortDescription'];
        }

        return '(No documentation available)';
    }

    /**
     * @param array $functionInfo
     *
     * @return string|null
     */
    protected function generateLongDescription(array $functionInfo): ?string
    {
        if (!empty($functionInfo['longDescription'])) {
            return "# Description\n" . $functionInfo['longDescription'];
        }

        return null;
    }

    /**
     * @param array $functionInfo
     *
     * @return string|null
     */
    protected function generateParameters(array $functionInfo): ?string
    {
        $parameterLines = [];

        if (empty($functionInfo['parameters'])) {
            return null;
        }

        foreach ($functionInfo['parameters'] as $parameter) {
            $parameterLines[] = $this->generateParameterLine($parameter);
        }

        // The header symbols seem to be required for some markdown parser, such as npm's marked.
        $table =
            "   |   |   \n" .
            "--- | --- | ---\n" .
            implode("\n", $parameterLines);

        return "# Parameters\n" . $table;
    }

    /**
     * @param array $parameter
     *
     * @return string
     */
    protected function generateParameterLine(array $parameter): string
    {
        $parameterColumns = [];

        $name = '';
        $name .= '•&nbsp;';

        if ($parameter['isOptional']) {
            $name .= '[';
        }

        $name .= $this->parameterNamePrettyPrinter->print($parameter);

        if ($parameter['isOptional']) {
            $name .= ']';
        }

        $parameterColumns[] = '**' . $name . '**';

        if (!empty($parameter['types'])) {
            $value = $this->tooltipTypeListPrettyPrinter->print(array_map(function (array $type) {
                return $type['type'];
            }, $parameter['types']));

            $parameterColumns[] = '*' . $value . '*';
        } else {
            $parameterColumns[] = ' ';
        }

        if ($parameter['description']) {
            $parameterColumns[] = $parameter['description'];
        } else {
            $parameterColumns[] = ' ';
        }

        return implode(' | ', $parameterColumns);
    }

    /**
     * @param array $functionInfo
     *
     * @return string
     */
    protected function generateReturn(array $functionInfo): string
    {
        $returnDescription = null;

        if (!empty($functionInfo['returnTypes'])) {
            $value = $this->tooltipTypeListPrettyPrinter->print(array_map(function (array $type) {
                return $type['type'];
            }, $functionInfo['returnTypes']));

            $returnDescription = '*' . $value . '*';

            if ($functionInfo['returnDescription']) {
                $returnDescription .= ' &mdash; ' . $functionInfo['returnDescription'];
            }
        } else {
            $returnDescription = '(Not known)';
        }

        return "# Returns\n{$returnDescription}";
    }

    /**
     * @param array $functionInfo
     *
     * @return string|null
     */
    protected function generateThrows(array $functionInfo): ?string
    {
        $throwsLines = [];

        foreach ($functionInfo['throws'] as $throwsItem) {
            $throwsColumns = [];

            $throwsColumns[] = "•&nbsp;**{$throwsItem['type']}**";

            if ($throwsItem['description']) {
                $throwsColumns[] = $throwsItem['description'];
            } else {
                $throwsColumns[] = ' ';
            }

            $throwsLines[] = implode(' | ', $throwsColumns);
        }

        if (empty($throwsLines)) {
            return null;
        }

        // The header symbols seem to be required for some markdown parser, such as npm's marked.
        $table =
            "   |   |   \n" .
            "--- | --- | ---\n" .
            implode("\n", $throwsLines);

        return "# Throws\n" . $table;
    }
}
