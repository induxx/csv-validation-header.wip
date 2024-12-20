<?php

namespace Misery\Component\Action;

use Misery\Model\DataStructure\ItemInterface;

interface ActionItemInterface
{
    public function applyAsItem(ItemInterface $item): ItemInterface;
}