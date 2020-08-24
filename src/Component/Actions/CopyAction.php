<?php

namespace Misery\Component\Actions;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class CopyAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'copy';

    /** @var array */
    private $options = [
        'from' => null,
        'to' => null,
    ];

    public function apply(array $item): array
    {
        return $item;
    }
}