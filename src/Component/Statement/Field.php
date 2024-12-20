<?php

namespace Misery\Component\Statement;

class Field
{
    private $field;
    private $value;

    public function __construct(string $field, string $value = null)
    {
        $this->field = str_replace(['{','}'], '', $field); # for legacy reasons we can't replace this value here yet
        $this->value = $value;
    }

    public function repopulateFromItem(array $item): void
    {
        if (ItemPlaceholder::matches($this->value)) {
            $this->value = ItemPlaceholder::replace($this->value, $item);
        }
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue(): ? string
    {
        return $this->value;
    }
}