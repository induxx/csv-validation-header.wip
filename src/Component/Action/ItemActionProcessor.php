<?php

namespace Misery\Component\Action;

use Misery\Model\DataStructure\ItemInterface;

class ItemActionProcessor
{
    private array $configurationRules;

    public function __construct(array $configurationRules)
    {
        $this->configurationRules = $configurationRules;
    }

    public function process(ItemInterface|array $item): ItemInterface|array
    {
        foreach ($this->configurationRules as $action) {
            if ($item instanceof ItemInterface) {
                $item = $action->applyAsItem($item);
                continue;
            }
            $item = $action->apply($item);
        }

        return $item;
    }
}