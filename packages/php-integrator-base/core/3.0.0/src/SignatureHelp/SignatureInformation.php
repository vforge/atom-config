<?php

namespace PhpIntegrator\SignatureHelp;

use JsonSerializable;

/**
 * Represent a single potential signature in the context of signature help.
 *
 * This is a value object and immutable.
 *
 * @see https://github.com/Microsoft/language-server-protocol/blob/164eb31bd49535dca046034602146f96bb17b56d/protocol.md#signature-help-request
 */
class SignatureInformation implements JsonSerializable
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
     * @var ParameterInformation[]|null
     */
    private $parameters;

    /**
     * @param string                      $label
     * @param string|null                 $documentation
     * @param ParameterInformation[]|null $parameters
     */
    public function __construct(string $label, ?string $documentation, ?array $parameters)
    {
        $this->label = $label;
        $this->documentation = $documentation;
        $this->parameters = $parameters;
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
     * @return ParameterInformation[]|null
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'label'         => $this->getLabel(),
            'documentation' => $this->getDocumentation(),
            'parameters'    => $this->getParameters()
        ];
    }
}
