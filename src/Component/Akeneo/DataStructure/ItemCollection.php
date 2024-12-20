<?php

namespace Misery\Component\Akeneo\DataStructure;

use App\Component\Akeneo\Api\Objects\Scope;
use Misery\Component\Converter\Matcher;

/**
 * This Item is part of the Matcher Family
 * It is NOT part of akeneo, The GOAL is that anything can be converted and reverted into this Object
 */
class ItemCollection
{
    /** @var Item[]|array $items */
    private array $items;

    public function __construct(array $items = [])
    {
        foreach ($items as $name => $value) {
            $this->addItem($name, $value);
        }
    }

    public function addItem(string $code, $itemValue, array $context = [])
    {
        $this->items[$code] = Item::withContext($code, $itemValue, $context);
    }

    public function copyItem(string $fromAttributeCode, string $toAttributeCode, $dataValue = null): void
    {
        $item = $this->getItem($fromAttributeCode);
        if (!$item) {
            return;
        }

        $matcher = $item->getMatcher()->duplicateWithNewKey($toAttributeCode);

        $itemValue = $item->getValue();
        // setting some values
        $itemValue['matcher'] = $matcher;
        $itemValue['type'] = $item->getType();
        if (isset($dataValue)) {
            $itemValue['data'] = $dataValue;
        }

        $this->addItem($matcher->getMainKey(), $itemValue, $item->getContext());
    }

    public function editItemValue(string $code, $dataValue): void
    {
        $item = $this->getItem($code);
        $itemValue = $item->getValue();
        $itemValue['data'] = $dataValue;
        $this->addItem($item->getCode(), $itemValue, $item->getContext());
    }

    /**
     * Removes a value for a given attribute code.
     *
     * @param string $code The attribute code to remove.
     * @return void
     */
    public function removeItem(string $code): void
    {
        unset($this->items[$code]);
    }

    public function getItem(string $code): ?Item
    {
        return $this->items[$code] ?? $this->getItemByMatch($code) ?? null;
    }
    /**
     * Gets all attributes.
     *
     * @return Item[] The array of all attributes.
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getItemsByScope(Scope $scope): \Generator
    {
        foreach ($this->items as $code => $item) {
            if ($item->getScope()->equals($type)) {
                yield $code => $item;
            }
        }
    }

    public function getItemByMatch(string $match): ?Item
    {
        foreach ($this->items as $code => $item) {
            if ($item->getMatcher()->matches($match)) {
                return $item;
            }
        }
        return null;
    }

    public function getItemsByMatch(string $match): \Generator
    {
        foreach ($this->items as $code => $item) {
            if ($item->getMatcher()->matches($match)) {
                yield $code => $item;
            }
        }
    }

    public function getItemsByType(string $type): \Generator
    {
        foreach ($this->items as $code => $item) {
            if ($item->getType() == $type) {
                yield $code => $item;
            }
        }
    }
}
