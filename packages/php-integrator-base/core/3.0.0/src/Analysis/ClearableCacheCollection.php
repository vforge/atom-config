<?php

namespace PhpIntegrator\Analysis;

/**
 * Collections of clearable cache objects that simply delegates clear cache commands to its elements (aggregate).
 */
class ClearableCacheCollection implements ClearableCacheInterface
{
    /**
     * @var ClearableCacheInterface[]
     */
    private $elements;

    /**
     * @param ClearableCacheInterface[] $elements
     */
    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    /**
     * @inheritDoc
     */
    public function clearCache(): void
    {
        foreach ($this->elements as $element) {
            $element->clearCache();
        }
    }
}
