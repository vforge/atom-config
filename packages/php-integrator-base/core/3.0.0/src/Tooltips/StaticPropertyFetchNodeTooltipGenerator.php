<?php

namespace PhpIntegrator\Tooltips;

use UnexpectedValueException;

use PhpIntegrator\Analysis\Node\PropertyFetchPropertyInfoRetriever;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Provides tooltips for {@see Node\Expr\StaticPropertyFetch} nodes.
 */
class StaticPropertyFetchNodeTooltipGenerator
{
    /**
     * @var PropertyFetchPropertyInfoRetriever
     */
    private $propertyFetchPropertyInfoRetriever;

    /**
     * @var PropertyTooltipGenerator
     */
    private $propertyTooltipGenerator;

    /**
     * @param PropertyFetchPropertyInfoRetriever $propertyFetchPropertyInfoRetriever
     * @param PropertyTooltipGenerator           $propertyTooltipGenerator
     */
    public function __construct(
        PropertyFetchPropertyInfoRetriever $propertyFetchPropertyInfoRetriever,
        PropertyTooltipGenerator $propertyTooltipGenerator
    ) {
        $this->propertyFetchPropertyInfoRetriever = $propertyFetchPropertyInfoRetriever;
        $this->propertyTooltipGenerator = $propertyTooltipGenerator;
    }

    /**
     * @param Node\Expr\StaticPropertyFetch $node
     * @param Structures\File               $file
     * @param string                        $code
     * @param int                           $offset
     *
     * @throws UnexpectedValueException
     *
     * @return string
     */
    public function generate(Node\Expr\StaticPropertyFetch $node, Structures\File $file, string $code, int $offset): string
    {
        $infoElements = $this->propertyFetchPropertyInfoRetriever->retrieve($node, $file, $code, $offset);

        if (empty($infoElements)) {
            throw new UnexpectedValueException('No property fetch information was found for node');
        }

        // Fetch the first tooltip. In theory, multiple tooltips are possible, but we don't support these at the moment.
        $info = array_shift($infoElements);

        return $this->propertyTooltipGenerator->generate($info);
    }
}
