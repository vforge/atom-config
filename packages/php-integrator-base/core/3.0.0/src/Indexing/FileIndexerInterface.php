<?php

namespace PhpIntegrator\Indexing;

/**
 * Handles indexation of PHP code in a single file.
 *
 * The index only contains "direct" data, meaning that it only contains data that is directly attached to an element.
 * For example, classes will only have their direct members attached in the index. The index will also keep track of
 * links between structural elements and parents, implemented interfaces, and more, but it will not duplicate data,
 * meaning parent methods will not be copied and attached to child classes.
 *
 * The index keeps track of 'outlines' that are confined to a single file. It in itself does not do anything
 * "intelligent" such as automatically inheriting docblocks from overridden methods.
 */
interface FileIndexerInterface
{
    /**
     * @param string $filePath
     * @param string $code
     *
     * @throws IndexingFailedException
     *
     * @return void
     */
    public function index(string $filePath, string $code): void;
}
