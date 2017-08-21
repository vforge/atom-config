<?php

namespace PhpIntegrator\Parsing;

use PhpParser\Parser;
use PhpParser\ErrorHandler;

/**
 * Parser that caches nodes to avoid parsing the same file or source code multiple times.
 *
 * Only the last parsed result is retained. If different code is passed, the cache will miss and a new parse call will
 * occur.
 */
class CachingParser implements Parser
{
    /**
     * @var Parser
     */
    private $proxiedObject;

    /**
     * @var array|null
     */
    private $cache = null;

    /**
     * @var string|null
     */
    private $lastCacheKey = null;

    /**
     * @var ErrorHandler\Collecting
     */
    private $errorHandler = null;

    /**
     * @param Parser $proxiedObject
     */
    public function __construct(Parser $proxiedObject)
    {
        $this->proxiedObject = $proxiedObject;
    }

    /**
     * @inheritDoc
     */
    public function parse(string $code, ErrorHandler $errorHandler = null)
    {
        if (!$errorHandler instanceof ErrorHandler\Collecting) {
            // Throwing error handlers need to throw on every call, if we cache the result, throws won't happen anymore
            // and behavior changes. With a collecting handler, we can reproduce the previous errors each time.
            return $this->proxiedObject->parse($code, $errorHandler);
        }

        $cacheKey = md5($code);

        if ($cacheKey === $this->lastCacheKey && $this->cache !== null) {
            $this->copyCollectedErrorsTo($errorHandler);

            return $this->cache;
        }

        $this->errorHandler = new ErrorHandler\Collecting();
        $this->cache = $this->proxiedObject->parse($code, $this->errorHandler);
        $this->lastCacheKey = $cacheKey;

        $this->copyCollectedErrorsTo($errorHandler);

        return $this->cache;
    }

    /**
     * @param ErrorHandler\Collecting $errorHandler
     */
    protected function copyCollectedErrorsTo(ErrorHandler\Collecting $errorHandler): void
    {
        $errorHandler->clearErrors();

        foreach ($this->errorHandler->getErrors() as $error) {
            $errorHandler->handleError($error);
        }
    }
}
