<?php

namespace Misery\Model\DataStructure;

use Assert\Assert;

/**
 * This Item is part of the Matcher Family
 * It is NOT part of akeneo, The GOAL is that anything can be converted and reverted into this Object
 */
class Item implements ItemInterface
{
    public function __construct(private array $itemNodes = [])
    {
        foreach ($this->itemNodes as $node) {
            Assert::that($node)->isInstanceOf(ItemNode::class);
        }
    }

    public function addItem(string $code, mixed $itemValue, array $context = []): void
    {
        $this->itemNodes[$code] = ItemNode::withContext($code, $itemValue, $context);
    }

    public function copyItem(string $fromCode, string $toCode): void
    {
        $item = $this->getItem($fromCode);
        if (!$item) {
            return;
        }

        $itemValue = $item->getValue();

        // Property Values
        if (is_string($itemValue)) {
            $this->addItem($toCode, $itemValue, $item->getContext());
            return;
        }

        $matcher = $item->getMatcher()->duplicateWithNewKey($toCode);

        $itemValue['type'] = $item->getType();
        $itemValue['matcher'] = $matcher;

        $this->addItem($toCode, $itemValue, $item->getContext());
    }

    public function moveItem(string $fromCode, string $toCode): void
    {
        $this->copyItem($fromCode, $toCode);
        $this->removeItem($fromCode);
    }

    public function editItemValue(string $code, $dataValue): void
    {
        $item = $this->getItem($code);
        $itemValue = $item->getValue();
        if (is_string($itemValue)) {
            $itemValue = $dataValue;
        } else {
            $itemValue['data'] = $dataValue;
        }
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
        if ($this->hasItem($code)) {
            unset($this->itemNodes[$code]);
        }
    }

    public function hasItem(string $code): bool
    {
        return array_key_exists($code, $this->itemNodes) ?? $this->getItemByMatch($code) !== null;
    }

    public function getItem(string $codeOrMatch): ?ItemNode
    {
        return $this->itemNodes[$codeOrMatch] ?? $this->getItemByMatch($codeOrMatch) ?? null;
    }

    /**
     * Gets all attributes.
     *
     * @return Item[] The array of all attributes.
     */
    public function getItemNodes(): array
    {
        return $this->itemNodes;
    }

    public function getItemCodes(): array
    {
        return array_keys($this->itemNodes);
    }

    public function getItemsByScope(ScopeInterface $scope): \Generator
    {
        foreach ($this->itemNodes as $code => $item) {
            if ($item->getScope()->equals($scope)) {
                yield $code => $item;
            }
        }
    }

    public function getItemByMatch(string $match): ?ItemNode
    {
        foreach ($this->itemNodes as $code => $item) {
            if ($item->getMatcher()->matches($match)) {
                return $item;
            }
        }
        return null;
    }

    public function getItemsByMatch(string $match): \Generator
    {
        foreach ($this->itemNodes as $code => $item) {
            if ($item->getMatcher()->matches($match)) {
                yield $code => $item;
            }
        }
    }

    public function getItemsByType(string $type): \Generator
    {
        foreach ($this->itemNodes as $code => $item) {
            if ($item->getType() == $type) {
                yield $code => $item;
            }
        }
    }
}
