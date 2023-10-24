<?php

namespace Misery\Component\Converter\Akeneo\Csv;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Converter\AkeneoCsvHeaderContext;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Converter\Matcher;
use Misery\Component\Encoder\ItemEncoder;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Format\StringToBooleanFormat;
use Misery\Component\Format\StringToListFormat;

class Product implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private AkeneoCsvHeaderContext $csvHeaderContext;
    private ItemEncoder $encoder;

    private $options = [
        'container' => 'values',
        'attribute_types:list' => null, # this key value list is optional, improves type matching for options, metrics, prices
        'properties' => [
            'sku' => [
                'text' => null,
            ],
            'enabled' => [
                'boolean' => null,
            ],
            'family' => [
                'text' => null,
            ],
            'categories'=> [
                'list' => null,
            ],
            'parent' => [
                'text' => null,
            ],
        ],
        'parse' => [],
    ];

    public function __construct(AkeneoCsvHeaderContext $csvHeaderContext)
    {
        $this->csvHeaderContext = $csvHeaderContext;
        $this->encoder = $this->ItemEncoderFactory()->createItemEncoder([
            'encode' => $this->getOption('properties'),
            'parse' => $this->getOption('parse'),
        ]);
    }

    private function ItemEncoderFactory(): ItemEncoderFactory
    {
        $encoderFactory = new ItemEncoderFactory();

        $formatRegistry = new Registry('format');
        $formatRegistry
            ->register(StringToListFormat::NAME, new StringToListFormat())
            ->register(StringToBooleanFormat::NAME, new StringToBooleanFormat())
        ;

        $encoderFactory->addRegistry($formatRegistry);

        return $encoderFactory;
    }

    public function convert(array $item): array
    {
        $codes = $this->getOption('attribute_types:list');
        $keyCodes = is_array($codes) ? array_keys($codes): null;
        $separator = '-';
        $output = [];

        $item = $this->encoder->encode($item);

        foreach ($item as $key => $value) {
            $keys = explode($separator, $key);
            $masterKey = $keys[0];

            if (in_array($masterKey, array_keys($this->getOption('properties')))) {
                continue;
            }

            if ($keyCodes && false === in_array($masterKey, $keyCodes)) {
                continue;
            }

            if (str_ends_with($key, '-unit') !== false) {
                unset($item[$key]);
                continue;
            }

            # values
            $prep = $this->csvHeaderContext->create($item)[$key];
            $prep['data'] = $value;

            if (is_array($codes)) {
                # metrics
                if ($codes[$masterKey] === 'pim_catalog_metric') {
                    $prep['data'] = [
                        'amount' => $value,
                        'unit' => $item[str_replace($masterKey, $masterKey.'-unit', $key)] ?? null,
                    ];
                }
                # multiselect
                if ($codes[$masterKey] === 'pim_catalog_multiselect') {
                    $prep['data'] = array_filter(explode(',', $prep['data']));
                }
            }

            $matcher = Matcher::create('values|'.$masterKey, $prep['locale'], $prep['scope']);
            unset($prep['key']); // old way of storing the original key
            $output[$k = $matcher->getMainKey()] = $prep;
            $output[$k]['matcher'] = $matcher;

            unset($item[$key]);
        }

        return $item+$output;
    }

    public function revert(array $item): array
    {
        $container = $this->getOption('container');

        $output = [];
        $output['sku'] = $item['sku'] ?? $item['identifier'] ?? null;
        if (array_key_exists('family', $output)) {
            $output['family'] = $item['family'];
        }

        foreach ($item as $key => $itemValue) {
            $matcher = $itemValue['matcher'] ?? null;
            /** @var $matcher Matcher */
            if ($matcher && $matcher->matches($container)) {
                unset($itemValue['matcher']);
                unset($item[$key]);
                if (is_array($itemValue['data']) && array_key_exists('unit', $itemValue['data'])) {
                    $output[$matcher->getRowKey()] = $itemValue['data']['amount'];
                    $output[$matcher->getRowKey().'-unit'] = $itemValue['data']['unit'];
                    continue;
                }
                if (is_array($itemValue['data'])) {
                    $output[$matcher->getRowKey()] = implode(',', $itemValue['data']);
                    continue;
                }
                if (isset($itemValue['data'])) {
                    $output[$matcher->getRowKey()] = $itemValue['data'];
                }
            }
        }

        return $output;
    }

    public function getName(): string
    {
        return 'akeneo/product/csv';
    }
}