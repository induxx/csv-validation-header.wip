<?php

namespace Misery\Component\Filter;

use Assert\Assert;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;

class ItemTreeSortFilter
{
    static $indexes = [];
    static $config = [

        'id_field' => null,
        'parent_field' => null,
        'parent_value' => '',
        'sort_children_on' => [],
    ];

    public static function sort(ItemReader $main, array $configuration): ItemReader
    {
        $configuration = array_merge(static::$config, $configuration);
        Assert::that($configuration['id_field'])->string();
        Assert::that($configuration['parent_field'])->string();
        Assert::that($configuration['sort_children_on'])->isArray()->notEmpty();

        // $items = self::sortItems($items, $on, $order);

        // find the root items (empty parent_id) and sort them

        // find the next items (non-empty parent_id) and sort them (recursively)

        static::recursiveFind($main, $configuration);

        return $main->index(
            array_merge(...static::$indexes)
        );
    }

    private static function recursiveFind(ItemReader $main, array $configuration, int $level = 0): void
    {
        $level++;
        $idField = $configuration['id_field'];
        $parentField = $configuration['parent_field'];
        $parentValue = $configuration['parent_value'];
        $sortChildrenOn = $configuration['sort_children_on'];

        $sub = $main->find([$parentField => $parentValue]);
        $items = new ItemCollection($sub->getItems());

        if ($items->count() > 0) {
            $sortedReader = ItemSortFilter::sort(new ItemReader($items), $sortChildrenOn, $configuration);
            $items = $sortedReader->getItems();
            static::$indexes[$level] = array_merge(static::$indexes[$level] ?? [], array_keys($items));
            $sortedItems = new ItemCollection($items);

            while ($sortedItems->valid()) {
                $item = $sortedItems->current();
                $configuration['parent_value'] = $item[$idField];

                static::recursiveFind($main, $configuration, $level);

                $sortedItems->next();
            }
        }
    }
}