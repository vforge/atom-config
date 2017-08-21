<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Interface for classes that can deduce the type of a node.
 */
interface NodeTypeDeducerInterface
{
    /**
     * @param Node            $node
     * @param Structures\File $file
     * @param string          $code
     * @param int             $offset
     *
     * @throws UnexpectedValueException when a node of an unexpected type is encountered (i.e. the deducer can't handle
     *                                  the passed node type).
     *
     * @return string[]
     */
    public function deduce(Node $node, Structures\File $file, string $code, int $offset): array;
}
