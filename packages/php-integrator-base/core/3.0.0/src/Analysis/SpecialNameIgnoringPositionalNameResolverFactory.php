<?php

namespace PhpIntegrator\Analysis;

use PhpIntegrator\Analysis\Typing\TypeAnalyzer;

use PhpIntegrator\Common\FilePosition;

use PhpIntegrator\NameQualificationUtilities\PositionalNameResolverInterface;
use PhpIntegrator\NameQualificationUtilities\StructureAwareNameResolverFactoryInterface;

/**
 * Factory that creates instances of a {@see SpecialNameIgnoringPositionalNameResolver}.
 */
class SpecialNameIgnoringPositionalNameResolverFactory implements StructureAwareNameResolverFactoryInterface
{
    /**
     * @var StructureAwareNameResolverFactoryInterface
     */
    private $delegate;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @param StructureAwareNameResolverFactoryInterface $delegate
     * @param TypeAnalyzer                               $typeAnalyzer
     */
    public function __construct(
        StructureAwareNameResolverFactoryInterface $delegate,
        TypeAnalyzer $typeAnalyzer
    ) {
        $this->delegate = $delegate;
        $this->typeAnalyzer = $typeAnalyzer;
    }

    /**
     * @inheritDoc
     */
    public function create(FilePosition $filePosition): PositionalNameResolverInterface
    {
        $positionalNameResolver = $this->delegate->create($filePosition);

        return new SpecialNameIgnoringPositionalNameResolver(
            $positionalNameResolver,
            $this->typeAnalyzer
        );
    }
}
