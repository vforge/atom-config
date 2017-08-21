<?php

namespace PhpIntegrator\Indexing;

/**
 * Enumeration of indexing event names.
 */
class IndexingEventName
{
    /**
     * @var string
     */
    public const NAMESPACE_UPDATED = 'namespaceUpdated';

    /**
     * @var string
     */
    public const NAMESPACE_REMOVED = 'namespaceRemoved';

    /**
     * @var string
     */
    public const IMPORT_INSERTED = 'importInserted';

    /**
     * @var string
     */
    public const CONSTANT_UPDATED = 'constantUpdated';

    /**
     * @var string
     */
    public const CONSTANT_REMOVED = 'constantRemoved';

    /**
     * @var string
     */
    public const FUNCTION_UPDATED = 'functionUpdated';

    /**
     * @var string
     */
    public const FUNCTION_REMOVED = 'functionRemoved';

    /**
     * @var string
     */
    public const STRUCTURE_UPDATED = 'structureUpdated';

    /**
     * @var string
     */
    public const STRUCTURE_REMOVED = 'structureRemoved';
}
