<?php

namespace PhpIntegrator\SignatureHelp;

use JsonSerializable;

/**
 * Represents a parameter in a signature in the context of signature help.
 *
 * This is a value object and immutable.
 *
 * @see https://github.com/Microsoft/language-server-protocol/blob/164eb31bd49535dca046034602146f96bb17b56d/protocol.md#signature-help-request
 */
class ParameterInformation implements JsonSerializable
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string|null
     */
    private $documentation;

    /**
     * @param string      $label
     * @param string|null $documentation
     */
    public function __construct(string $label, ?string $documentation)
    {
        $this->label = $label;
        $this->documentation = $documentation;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string|null
     */
    public function getDocumentation(): ?string
    {
        return $this->documentation;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'label'         => $this->getLabel(),
            'documentation' => $this->getDocumentation()
        ];
    }
}
