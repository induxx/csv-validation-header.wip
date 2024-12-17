<?php

namespace Misery\Component\Mapping;

/**
 * Class ColumnMapper
 * @package Misery\Component\Mapping
 */
class ColumnMapper implements Mapper
{
    public function __construct(private readonly bool $strictMode = true) {}

    public function map(array $item, array $mappings)
    {
        // Check for missing mapped items
        $mappingDif = count(array_diff(array_keys($mappings), array_keys($item))) == count(array_keys($mappings));

        if ($this->strictMode && $mappingDif) {
            throw new \InvalidArgumentException(sprintf(
                'No mapped items %s are not found in item.',
                json_encode($mappings)
            ));
        }

        $keys = [];
        foreach ($item as $key => $value) {
            if (isset($mappings[$key])) {
                $keys[] = $mappings[$key];
                continue;
            }
            $keys[] = $key;
        }

        return array_combine($keys, array_values($item));
    }

    public function mapWithSuffix(
        array $item,
        string $suffix,
        array $excludeList,
        array $filterList
    ) {
        $itemToReturn = [];
        foreach ($item as $key => $value) {
            if (!empty($filterList) && !in_array($key, $filterList)) {
                $itemToReturn[$key] = $value;

                continue;
            }

            if (in_array($key, $excludeList)) {
                $itemToReturn[$key] = $value;

                continue;
            }

            $itemToReturn[$key.$suffix] = $value;
        }

        return $itemToReturn;
    }
}
