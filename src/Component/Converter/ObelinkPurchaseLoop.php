<?php
declare(strict_types=1);

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Reader\ItemCollection;

class ObelinkPurchaseLoop implements ConverterInterface, RegisteredByNameInterface, OptionsInterface, ItemCollectionLoaderInterface
{
    use OptionsTrait;

    private $options = [
        'list' => null,
    ];

    public function __construct(private readonly ConverterInterface $productConverter) {}

    public function load(array $item): ItemCollection
    {
        return new ItemCollection($this->convert($item));
    }

    public function convert(array $item): array
    {
        $result = [];

        $item = $this->productConverter->convert($item);

        $suppliers = 1;
        while($suppliers <= 5) {
            $supplierCode = $item['values|supplier_' . $suppliers]['data'] ?? null;
            $supplierDeliverCode = $item['values|supplier_'.$suppliers.'_deliver']['data'] ?? null;

            if(!$supplierCode || !$supplierDeliverCode){
                $suppliers++;
                continue;
            }

            $orderMultiplier = $item['values|supplier_'.$suppliers.'_amount_order_factor']['data'] ?? null;
            $item['suppliers_data'] = [
                'supplier_index' => $suppliers,
                'supplier_code' =>  (string) $supplierCode,
                'supplier_deliver-code' =>  (string) $supplierDeliverCode,
                'order_multiplier' =>  (int) $orderMultiplier,
            ];
            $suppliers++;
            $result[] = $item;
        }

        return $result;
    }

    public function revert(array $item): array
    {
       return $item;
    }

    public function getName(): string
    {
        return 'obelink/purchase/api';
    }
}
