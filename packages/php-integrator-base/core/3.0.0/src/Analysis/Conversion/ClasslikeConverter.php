<?php

namespace PhpIntegrator\Analysis\Conversion;

use PhpIntegrator\Indexing\Structures;

/**
 * Converts raw classlike data from the index to more useful data.
 */
class ClasslikeConverter extends AbstractConverter
{
    /**
     * @param Structures\Structure $structure
     *
     * @return array
     */
    public function convert(Structures\Structure $structure): array
    {
        $data = [
            'name'               => $structure->getName(),
            'fqcn'               => $structure->getFqcn(),
            'startLine'          => $structure->getStartLine(),
            'endLine'            => $structure->getEndLine(),
            'filename'           => $structure->getFile()->getPath(),
            'type'               => $structure->getTypeName(),
            'isDeprecated'       => $structure->getIsDeprecated(),
            'hasDocblock'        => $structure->getHasDocblock(),
            'hasDocumentation'   => $structure->getHasDocblock(),
            'shortDescription'   => $structure->getShortDescription(),
            'longDescription'    => $structure->getLongDescription()
        ];

        if ($structure instanceof Structures\Class_) {
            $data['isAbstract']   = $structure->getIsAbstract();
            $data['isFinal']      = $structure->getIsFinal();
            $data['isAnnotation'] = $structure->getIsAnnotation();
        }

        return $data;
    }
}
