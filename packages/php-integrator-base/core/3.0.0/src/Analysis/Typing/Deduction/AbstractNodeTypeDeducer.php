<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

/**
 * Abstract base class for node type deducers.
 */
abstract class AbstractNodeTypeDeducer implements NodeTypeDeducerInterface
{
    /**
     * @param array $typeArray
     *
     * @return string|null
     */
    protected function fetchResolvedTypeFromTypeArray(array $typeArray): ?string
    {
        return $typeArray['resolvedType'];
    }

    /**
     * @param array $typeArrays
     *
     * @return string[]
     */
    protected function fetchResolvedTypesFromTypeArrays(array $typeArrays): array
    {
        return array_map([$this, 'fetchResolvedTypeFromTypeArray'], $typeArrays);
    }
}
