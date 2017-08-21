<?php

namespace PhpIntegrator\SignatureHelp;

use JsonSerializable;

/**
 * Represents signature help.
 *
 * Returned as the result of a signature help request.
 *
 * This is a value object and immutable.
 *
 * @see https://github.com/Microsoft/language-server-protocol/blob/164eb31bd49535dca046034602146f96bb17b56d/protocol.md#signature-help-request
 */
class SignatureHelp implements JsonSerializable
{
    /**
     * @var SignatureInformation[]
     */
    private $signatures;

    /**
     * @var int|null
     */
    private $activeSignature;

    /**
     * @var int|null
     */
    private $activeParameter;

    /**
     * @param SignatureInformation[] $signatures
     * @param int|null               $activeSignature
     * @param int|null               $activeParameter
     */
    public function __construct(array $signatures, ?int $activeSignature, ?int $activeParameter)
    {
        $this->signatures = $signatures;
        $this->activeSignature = $activeSignature;
        $this->activeParameter = $activeParameter;
    }

    /**
     * @return SignatureInformation[]
     */
    public function getSignatures(): array
    {
        return $this->signatures;
    }

    /**
     * @return int|null
     */
    public function getActiveSignature(): ?int
    {
        return $this->activeSignature;
    }

    /**
     * @return int|null
     */
    public function getActiveParameter(): ?int
    {
        return $this->activeParameter;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'signatures'      => $this->getSignatures(),
            'activeSignature' => $this->getActiveSignature(),
            'activeParameter' => $this->getActiveParameter()
        ];
    }
}
