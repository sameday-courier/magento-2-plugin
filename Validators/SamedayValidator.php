<?php

namespace SamedayCourier\Shipping\Validators;

use Magento\Framework\Validator\AbstractValidator;

class SamedayValidator extends AbstractValidator
{
    /**
     * @var bool $isValid
     */
    private $isValid;

    /**
     * @var string $message
     */
    private $message = 'Something went wrong!';

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessages(): array
    {
        $messages = parent::getMessages();
        $messages[] = $this->message;

        return $messages;
    }

    public function setValidation(bool $isValid): self
    {
        $this->isValid = $isValid;

        return $this;
    }

    public function getValidation(): bool
    {
        return $this->isValid;
    }

    public function isValid($value): bool
    {
        return $this->getValidation();
    }
}
