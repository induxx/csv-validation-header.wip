<?php

namespace Misery\Component\Akeneo\DataStructure;


use App\Component\Akeneo\Api\Objects\Product;
use Misery\Component\Converter\Matcher;

class BuildProductMatcher
{
    /**
     * Convert multi-dimensional structure data to a Single Dimensional Structure with Matcher
     *
     * Product Payload
     *
     * {
     *   "values": [
     *     "color": [
     *       {
     *         "data": "red",
     *         "locale": null,
     *         "scope": null,
     *       }
     *     ]
     *   ]
     * }
     *
     * Becomes
     *
     * {
     *   "values|color": {
     *         "data": "red",
     *         "locale": null,
     *         "scope": null,
     *         "matcher": {"sep":"|", "matches":["values", "color"], "scope":null, "locale":null}
     *    }
     * }
     *  - Scope-able & localizable: "values|color|nl_BE|ecom"
     *  - Scope-able: "values|color|ecom"
     *  - localizable: "values|color|nl_BE"
     **/
    public static function fromApiPayload(array $productData, array $attributeData = []): array
    {
        $newProduct = [];
        foreach ($productData['values'] ?? [] as $attributeCode => $productValues) {
            foreach ($productValues as $productValue) {
                $attribute = $attributeData[$attributeCode] ?? [];
                $matcher = Matcher::create('values|'.$attributeCode, $productValue['locale'], $productValue['scope']);
                $newProduct[$k = $matcher->getMainKey()] = $productValue + $attribute;
                $newProduct[$k]['matcher'] = $matcher;
            }
        }
        unset($productData['values']);

        return $newProduct + $productData;
    }

    public static function fromItem(ItemCollection $item)
    {
        $fields = [];
        foreach ($item->getItems() as $code => $fieldValue) {
            $matcher = $fieldValue->getMatcher();

            if ($matcher->matches('values')) {
                $fields['values'][$matcher->getPrimaryKey()][] = $fieldValue->getValue();
            } else {
                $fields[$code] = $fieldValue->getValue();
            }
        }

        return $fields;
    }

    public static function revertMatcherToProduct(array $productData): array
    {
        $fields = [];
        foreach ($productData ?? [] as $field => &$fieldValue) {
            $matcher = $fieldValue['matcher'] ?? null;
            if ($matcher instanceof Matcher) {
                unset($fieldValue['matcher']);
                $fields['values'][$matcher->getPrimaryKey()][] = $fieldValue;
            } else {
                $fields[$field] = $fieldValue;
            }
        }

        return $fields;
    }

    public static function revertToKeyValue(array $productData): array
    {
        $fields = [];
        foreach ($productData ?? [] as $field => $fieldValue) {
            $fields[$field] = $fieldValue;
            $matcher = $fieldValue['matcher'] ?? null;
            if ($matcher instanceof Matcher) {
                $fields[$field] = $fieldValue['data'] ?? null;
            }
        }

        return $fields;
    }
}