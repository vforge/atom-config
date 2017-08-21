<?php

namespace PhpIntegrator\Sockets;

use JsonSerializable;

/**
 * A response in JSON-RPC 2.0 format.
 *
 * Value object.
 */
class JsonRpcResponse implements JsonSerializable
{
    /**
     * @var string
     */
    private $jsonrpc;

    /**
     * @var string|int|null
     */
    private $id;

    /**
     * @var mixed|null
     */
    private $result;

    /**
     * @var object|null
     */
    private $error;

    /**
     * @param string|int|null $id
     * @param mixed|null      $result
     * @param object|null     $error
     * @param string          $jsonrpc
     */
    public function __construct($id, $result = null, $error = null, string $jsonrpc = '2.0')
    {
        $this->id = $id;
        $this->result = $result;
        $this->error = $error;
        $this->jsonrpc = $jsonrpc;
    }

    /**
     * @return string
     */
    public function getJsonrpc(): string
    {
        return $this->jsonrpc;
    }

    /**
     * @return string|int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed|null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return object|null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param array $array
     *
     * @return static
     */
    public static function createFromArray(array $array)
    {
        return new static(
            $array['id'],
            isset($array['result']) ? $array['result'] : null,
            isset($array['error']) ? $array['error'] : null,
            $array['jsonrpc']
        );
    }

    /**
     * @param string $json
     *
     * @return static
     */
    public static function createFromJson(string $json)
    {
        $data = json_decode($this->request['content'], true);

        return static::createFromArray($data);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $data = [
            'jsonrpc' => $this->getJsonrpc(),
            'id'      => $this->getId()
        ];

        if ($this->getError() !== null) {
            $data['error'] = $this->getError();
        } else {
            $data['result'] = $this->getResult();
        }

        return $data;
    }
}
