<?php

namespace Misery\Component\Akeneo\Client\Errors;

/**
 * @deprecated
 * @orig https://github.com/induxx/shared-app-libraries/blob/main/src/Component/Akeneo/Api/Client/Errors/
 */
class AkeneoError
{
    public function __construct(
        private readonly string $field,
        private readonly string $message,
        private readonly ?string $attribute = null,
        private readonly ?string $locale = null,
        private readonly ?string $scope = null
    ) {}

    public function getField(): string
    {
        return $this->field;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getAttributeCode(): ?string
    {
        return $this->attribute;
    }

    public function getLocale():? string
    {
        return $this->locale;
    }

    public function getScope():? string
    {
        return $this->scope;
    }
}