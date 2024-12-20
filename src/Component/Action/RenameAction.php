<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Converter\Matcher;
use Misery\Component\Mapping\ColumnMapper;
use Misery\Model\DataStructure\ItemInterface;

class RenameAction implements OptionsInterface
{
    use OptionsTrait;

    private $mapper = null;

    public const NAME = 'rename';

    /** @var array */
    private $options = [
        'from' => null,
        'to' => null,
        'suffix' => null,
        'exclude_list' => [],
        'filter_list' => null,
        'fields' => [],
        'strict_mode' => true,
    ];

    public function applyAsItem(ItemInterface $item): ItemInterface
    {
        $from = $this->getOption('from');
        $to = $this->getOption('to');
        $item->moveItem($from, $to);

        return $item;
    }

    public function init(): void
    {
        if (empty($this->mapper)) {
            $this->mapper = new ColumnMapper($this->getOption('strict_mode'));
        }
    }

    public function apply(array $item): array
    {
        $this->init();

        $from = $this->getOption('from');
        $to = $this->getOption('to');

        $from = $this->findMatchedValueData($item, $from) ?? $from;

        if (!isset($item[$from])) {
            return $item;
        }

        if (!empty($this->options['suffix'])) {
            return $this->mapper->mapWithSuffix(
                $item,
                $this->options['suffix'],
                $this->options['exclude_list'],
                $this->options['filter_list'] ?? $this->options['fields'],
            );
        }

        $fields = $this->getOption('fields');
        if ($fields !== []) {
            return $this->mapper->map($item, $fields);
        }

        return $this->mapper->map($item, [$from => $to]);
    }

    private function findMatchedValueData(array $item, string $field): int|string|null
    {
        foreach ($item as $key => $itemValue) {
            $matcher = $itemValue['matcher'] ?? null;
            /** @var $matcher Matcher */
            if ($matcher && $matcher->matches($field)) {
                return $key;
            }
        }

        return null;
    }
}