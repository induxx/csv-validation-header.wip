<?php

namespace Misery\Component\Common\Pipeline\Exception;

use Misery\Component\Akeneo\Client\Errors\AkeneoErrors;

class InvalidItemException extends \Exception
{
    private $invalidItem;
    private ?AkeneoErrors $errors;
    private $item;

    public function __construct(string $message = null, array $invalidItem, array $item = [])
    {
        $this->invalidItem = $invalidItem;
        $this->errors = $this->invalidItem['errors'] ?? null;
        unset($this->invalidItem['errors']);

        parent::__construct($message);
        $this->item = $item;
    }

    public function getItem(): array
    {
        return $this->item;
    }

    public function getInvalidItem(): array
    {
        return $this->invalidItem;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== null;
    }

    public function getErrors(): AkeneoErrors
    {
        return $this->errors;
    }
}