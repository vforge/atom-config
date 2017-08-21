<?php

namespace PhpIntegrator\Analysis\Conversion;

use PhpIntegrator\Indexing\Structures;

/**
 * Converts raw namespace data from the index to more useful data.
 */
class NamespaceConverter extends AbstractConverter
{
    /**
     * @param Structures\FileNamespace $namespace
     *
     * @return array
     */
    public function convert(Structures\FileNamespace $namespace): array
    {
        return [
            'name'      => $namespace->getName(),
            'file'      => $namespace->getFile()->getPath(),
            'startLine' => $namespace->getStartLine(),
            'endLine'   => $namespace->getEndLine()
        ];
    }
}
