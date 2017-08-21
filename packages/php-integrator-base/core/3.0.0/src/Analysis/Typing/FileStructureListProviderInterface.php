<?php

namespace PhpIntegrator\Analysis\Typing;

use RuntimeException;

use PhpIntegrator\Indexing\Structures;

/**
 * Interface for classes that can retrieve a structure list for a specific file.
 */
interface FileStructureListProviderInterface
{
    /**
     * @param Structures\File $file
     *
     * @throws RuntimeException
     *
     * @return array
     */
    public function getAllForFile(Structures\File $file): array;
}
