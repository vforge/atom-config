<?php

namespace PhpIntegrator\Sockets;

use JsonSerializable;

/**
 * An error in JSON-RPC 2.0 format.
 *
 * Value object.
 */
class JsonRpcError implements JsonSerializable
{
    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $message;

    /**
     * @var mixed|null
     */
    private $data;

    /**
     * @param int        $code
     * @param string     $message
     * @param mixed|null $data
     */
    public function __construct(int $code, string $message, $data = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return mixed|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $array
     *
     * @return static
     */
    public static function createFromArray(array $array)
    {
        return new static(
            $array['code'],
            $array['message'],
            isset($array['data']) ? $array['data'] : null
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
            'code'    => $this->getCode(),
            'message' => $this->getMessage()
        ];

        if ($this->getData() !== null) {
            $data['data'] = $this->getData();
        }

        return $data;
    }
}
