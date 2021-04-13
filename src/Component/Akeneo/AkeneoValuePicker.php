<?php

namespace Misery\Component\Akeneo;

use Misery\Component\Common\Picker\ValuePickerInterface;

/**
 *  expected/supported array values structures
 *
 * / local and scope
 *
 *     'description' => [
 *          'pim' => [
 *              'nl_BE' => 'LVS',
 *          ],
 *      ],
 *
 * / local
 *
 *     'description' => [
 *          'nl_BE' => 'LVS',
 *      ],
 *
 * / scope
 *
 *     'description' => [
 *          'pim' => 'LVS',
 *      ],
 *
 * / global
 *
 *     'description' => 'LVS',
 **/
class AkeneoValuePicker implements ValuePickerInterface
{
    private static $default = [
        'locale' => null,
        'scope' => null,
    ];

    public static function pick(array $item, string $key, array $context = [])
    {
        $context = array_merge(self::$default, $context);

        $itemValue = $item[$key] ?? null;

        if ($itemValue) {
            if (null === $context['scope'] && null === $context['locale']) {
                return $itemValue;
            }
            if ($context['scope'] && $context['locale'] && isset($itemValue[$context['scope']][$context['locale']])) {
                return $itemValue[$context['scope']][$context['locale']];
            }
            elseif ($context['scope'] && isset($itemValue[$context['scope']])) {
                return $itemValue[$context['scope']];
            }
            elseif ($context['locale'] && isset($itemValue[$context['locale']])) {
                return $itemValue[$context['locale']];
            }
        }

        return null;
    }
}