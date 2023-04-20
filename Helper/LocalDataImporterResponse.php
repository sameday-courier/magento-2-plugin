<?php

namespace SamedayCourier\Shipping\Helper;

class LocalDataImporterResponse
{
    /**
     * @var bool $succeed
     */
    private $succeed;

    /**
     * @var string $message
     */
    private $message;

    public function setSucceed(bool $succeed): self
    {
        $this->succeed = $succeed;

        return $this;
    }

    public function isSucceed(): bool
    {
        return $this->succeed;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
