<?php

namespace Misery\Component\Action;

use JetBrains\PhpStorm\NoReturn;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Model\DataStructure\ItemInterface;

class DebugAction implements OptionsInterface, ActionInterface, ActionItemInterface
{
    use OptionsTrait;

    public const NAME = 'debug';

    /** @var array */
    private $options = [];

    #[NoReturn] public function applyAsItem(ItemInterface $item): ItemInterface
    {
        dd($item);
    }

    public function apply(array $item): array
    {
        dd($item);
    }
}