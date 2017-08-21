<?php

namespace PhpIntegrator\Analysis;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

use PhpIntegrator\Common\FilePosition;

use PhpIntegrator\NameQualificationUtilities\NameKind;
use PhpIntegrator\NameQualificationUtilities\PositionalNameResolverInterface;

/**
 * Name resolver that can resolve docblock names to their FQCN.
 *
 * This class is also usable as a regular (non-docblock) type resolver as docblock names are a superset of standard
 * names. The additional functionality is decorated on top of the standard resolution process.
 */
class DocblockPositionalNameResolver implements PositionalNameResolverInterface
{
    /**
     * @var PositionalNameResolverInterface
     */
    private $delegate;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @param PositionalNameResolverInterface $delegate
     * @param TypeAnalyzer                    $typeAnalyzer
     */
    public function __construct(PositionalNameResolverInterface $delegate, TypeAnalyzer $typeAnalyzer)
    {
        $this->delegate = $delegate;
        $this->typeAnalyzer = $typeAnalyzer;
    }

    /**
     * @inheritDoc
     */
    public function resolve(string $name, FilePosition $filePosition, string $kind = NameKind::CLASSLIKE): string
    {
        if ($this->typeAnalyzer->isArraySyntaxTypeHint($name)) {
            $valueType = $this->typeAnalyzer->getValueTypeFromArraySyntaxTypeHint($name);

            $resolvedValueType = $this->delegate->resolve($valueType, $filePosition, $kind);

            return $resolvedValueType . '[]';
        }

        return $this->delegate->resolve($name, $filePosition, $kind);
    }
}
