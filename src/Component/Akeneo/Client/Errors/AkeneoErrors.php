<?php

namespace Misery\Component\Akeneo\Client\Errors;

/**
 * @deprecated
 * @orig https://github.com/induxx/shared-app-libraries/blob/main/src/Component/Akeneo/Api/Client/Errors/
 */
class AkeneoErrors
{
    private array $errors = [];

    public function __construct(private readonly string $message, array $errors)
    {
        foreach ($errors as $error) {
            $this->addError($error);
        }
    }

    private function addError(AkeneoError $error): void
    {
        $this->errors[] = $error;
    }

    public function getErrorMessages(): array
    {
        return array_map(function (AkeneoError $error) {
            return  $error->getField() . ': ' . $error->getMessage();
        }, $this->errors);
    }

    public function getAttributeCodes(): array
    {
        return array_filter(array_map(function (AkeneoError $error) {
            return $error->getAttributeCode();
        }, $this->errors));
    }

    public function printMessages(): string
    {
        return $this->message . ' ' . implode( '|', $this->getErrorMessages());
    }
}