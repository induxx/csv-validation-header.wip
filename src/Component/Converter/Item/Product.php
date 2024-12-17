<?php

namespace Misery\Component\Converter\Item;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Converter\AkeneoCsvHeaderContext;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Converter\Matcher;
use Misery\Component\Decoder\ItemDecoder;
use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Encoder\ItemEncoder;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Format\StringToBooleanFormat;
use Misery\Component\Format\StringToListFormat;

class Product implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private AkeneoCsvHeaderContext $csvHeaderContext;
    private ItemEncoder $encoder;
    private ItemDecoder $decoder;

    private $options = [];

    public function convert(array $item): array
    {
        return $item;
    }

    public function revert(array $item): array
    {
        $result = [];
        foreach ($item as $key => $value) {
            $result[$key] = is_array($value) ? $value['data'] ?? $value : $value;
        }

        return $result;
    }

    /**
     * COPY/PASTA \Misery\Component\Akeneo\AkeneoTypeBasedDataConverter
     */
    private function numberize($value)
    {
        if (is_integer($value)) {
            return $value;
        }
        if (is_float($value)) {
            return $value;
        }
        if (is_string($value)) {
            $posNum = str_replace(',', '.', $value);
            return is_numeric($posNum) ? $posNum: $value;
        }
    }

    public function getName(): string
    {
        return 'item/product';
    }
}