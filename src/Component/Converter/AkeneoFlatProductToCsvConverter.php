<?php

namespace Misery\Component\Converter;

use Misery\Component\Akeneo\Header\AkeneoHeaderTypes;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Reader\ReaderAwareInterface;
use Misery\Component\Reader\ReaderAwareTrait;

/**
 * This Converter converts flat product to std data
 * We focus on correcting with minimal issues
 * The better the input you give the better the output
 */
class AkeneoFlatProductToCsvConverter implements ConverterInterface, ReaderAwareInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;
    use ReaderAwareTrait;

    private $options = [
        'attribute:list' => [],
        'attribute_types:list' => [],
        'localizable_attribute_codes:list' => [],
        'scopable_attribute_codes:list' => [],
        'default_metrics:list' => [],
        'set_default_metrics' => false,
        'locales' => [],
        'default_locale' => null,
        'default_scope' => null,
        'default_currency' => null,
        'option_label' => 'label-nl_BE',
    ];

    private $decoder;

    public function convert(array $item): array
    {
        $tmp = [];
        // first we need to convert the values
        foreach ($item['values'] ?? $this->getProductValues($item) ?? [] as $key => $value) {
            $value = $this->getAkeneoDataStructure($key, $value);
            $matcher = Matcher::create('values|'.$key, $value['locale'], $value['scope']);
            $tmp[$key = $matcher->getMainKey()] = $value;
            $tmp[$key]['matcher'] = $matcher;
        }
        unset($item['values']);

        return $item;
    }

    public function getAkeneoDataStructure(string $attributeCode, $value): array
    {
        $type = $this->getOption('attribute_types:list')[$attributeCode] ?? null;
        if (null === $type) {
            return $value;
        }

        $localizable = in_array(
            $attributeCode,
            $this->getOption('localizable_attribute_codes:list')
        );
        $scopable = in_array(
            $attributeCode,
            $this->getOption('scopable_attribute_codes:list')
        );

        switch ($type) {
            case AkeneoHeaderTypes::TEXT:
                // no changes
                break;
            case AkeneoHeaderTypes::NUMBER:
                $value = $this->numberize($value);
                break;
            case AkeneoHeaderTypes::SELECT:
                $value = $this->findAttributeOptionCode($attributeCode, $value);
                break;
            case AkeneoHeaderTypes::MULTISELECT:
                $value = [$this->findAttributeOptionCode($attributeCode, $value)];
                break;
            case AkeneoHeaderTypes::METRIC:
                $amount = null;
                $unit = $this->getOption('default_metrics:list')[$attributeCode] ?? null;;
                if (is_numeric($value)) {
                    $amount = $this->numberize($value);
                }
                if (is_array($value)) {
                    if (array_key_exists('amount', $value)) {
                        $amount = $value['amount'];
                    }
                    if (array_key_exists('unit', $value)) {
                        $unit = $value['unit'];
                    }
                }

                $value = [
                    'amount' => $amount,
                    'unit' => $unit,
                ];
                break;
            case AkeneoHeaderTypes::PRICE:
                // no changes
                break;
        }

        return [
            'locale' => $localizable ? $this->getOption('default_locale') : null,
            'scope' => $scopable ? $this->getOption('default_scope') : null,
            'data' => $value,
        ];
    }

    private function numberize($value)
    {
        if (is_integer($value)) {
            return $value;
        }
        if (is_string($value)) {
            $posNum = str_replace(',', '.', $value);
            return is_numeric($posNum) ? $posNum: $value;
        }
    }

    /**
     * This function return the option_code that was made earlier
     * When generating option codes we expect a full export strategy
     */
    public function findAttributeOptionCode(string $attributeCode, string $optionLabel)
    {
        return $this->getReader()->find([
            'attribute' => $attributeCode,
            $this->getOption('option_label') => $optionLabel]
        )->getIterator()->current()['code'];
    }

    /**
     * This function will extract attribute values from the item based on the attribute:list
     */
    public function getProductValues(array $item): \Generator
    {
        foreach ($this->getOption('attribute:list') ?? [] as $attributeCode) {
            if (array_key_exists($attributeCode, $item)) {
                yield $attributeCode => $item[$attributeCode];
            }
        }
    }

    public function revert(array $item): array
    {
        $output = [];
        foreach ($item as $key => $itemValue) {
            $matcher = $itemValue['matcher'] ?? null;
            /** @var $matcher Matcher */
            if ($matcher && $matcher->matches('values')) {
                unset($itemValue['matcher']);
                unset($item[$key]);
                if (isset($itemValue['data']['unit'])) {
                    $output[$matcher->getRowKey()] = $itemValue['data']['amount'];
                    $output[$matcher->getRowKey().'-unit'] = $itemValue['data']['unit'];
                }
                if (is_string($itemValue['data'])) {
                    $output[$matcher->getRowKey()] = $itemValue['data'];
                }
            }
        }

        return $item+$output;
    }

    public function getName(): string
    {
        return 'flat/akeneo/product/csv';
    }
}