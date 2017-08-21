<?php

namespace PhpIntegrator\Sockets;

/**
 * An enumeration of JSON-RPC 2.0 error codes.
 */
class JsonRpcErrorCode
{
    /**
     * @var int
     */
    public const PARSE_ERROR                = -32700;

    /**
     * @var int
     */
    public const INVALID_REQUEST            = -32600;

    /**
     * @var int
     */
    public const METHOD_NOT_FOUND           = -32601;

    /**
     * @var int
     */
    public const INVALID_PARAMS             = -32602;

    /**
     * @var int
     */
    public const INTERNAL_ERROR             = -32603;

    /**
     * Indicates that a fatal error occurred in the srever.
     *
     * This is an error of the same origin as a LogicException in PHP. In other words, this was an exception that
     * indicates there is some sort of bug or problem in the server itself that should never have happened.
     *
     * @var int
     */
    public const FATAL_SERVER_ERROR         = -32000;

    /**
     * Indicates that a generic runtime error occurred in the server.
     *
     * This acts much like PHP's RuntimeException, i.e. this is an exception that does not indicate something went
     * horribly wrong, the execution of something has simply ceased because of an error condition.
     *
     * @var int
     */
    public const GENERIC_RUNTIME_ERROR      = -32001;

    /**
     * Indicates that the database version is of an incorrect (i.e. too old or too new) version and can't be used by
     * this version.
     *
     * The client should initiate a request to reinitialize the project in order to recreate the index database. This
     * could happen via the "initialize" command.
     *
     * @var int
     */
    public const DATABASE_VERSION_MISMATCH  = -32002;
}
