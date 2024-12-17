<?php

namespace Tests\Misery\Component\Akeneo\DataStructure;

use Misery\Component\Akeneo\DataStructure\AkeneoItemBuilder;
use Misery\Component\Converter\Matcher;
use Misery\Model\DataStructure\ItemInterface;
use PHPUnit\Framework\TestCase;

class AkeneoItemBuilderTest extends TestCase
{
    public function testFromProductApiPayload(): void
    {
        $productData = [
            'identifier' => '1234',
            'values' => [
                'weight' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [
                            'amount' => 1,
                            'unit' => 'GRAM'
                        ]
                    ]
                ],
                'color' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'ecom',
                        'data' => 'red',
                    ]
                ],
                'brand' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'nike'
                    ]
                ],
            ],
        ];

        $context = [
            'attribute_types' => [
                'weight' => 'metric',
                'color' => 'simple_select',
                'brand' => 'reference_data',
            ],
        ];

        // Call the method
        $item = AkeneoItemBuilder::fromProductApiPayload($productData, $context);

        // Assertions
        $this->assertInstanceOf(ItemInterface::class, $item);

        $this->assertSame(['identifier', 'values|weight', 'values|color|en_US|ecom', 'values|brand|en_US'], $item->getItemCodes());
        $colorData = $item->getItem('values|color|en_US|ecom')->getValue();

        $this->assertEquals(
            [
                'locale' => 'en_US',
                'scope' => 'ecom',
                'type' => 'simple_select',
                'matcher' => Matcher::create('values|color', 'en_US', 'ecom'),
                'data' => 'red',
            ],
            $colorData
        );

        $weightData = $item->getItem('values|weight')->getValue();

        $this->assertEquals(
            [
                'locale' => null,
                'scope' => null,
                'type' => 'metric',
                'matcher' => Matcher::create('values|weight'),
                'data' => [
                    'amount' => 1,
                    'unit' => 'GRAM'
                ],
            ],
            $weightData
        );
    }

    public function testFromCatalogApiPayload(): void
    {
        $catalogData = [
            'code' => 'black',
            'labels' => [
                'nl_BE' => 'zwart',
                'en_US' => 'black',
            ]
        ];

        // Call the method
        $item = AkeneoItemBuilder::fromCatalogApiPayload($catalogData, ['class' => 'attribute']);

        // Assertions
        $this->assertInstanceOf(ItemInterface::class, $item);

        $this->assertSame(['code', 'labels|nl_BE', 'labels|en_US'], $item->getItemCodes());

        $this->assertSame('black', $item->getItem('labels|en_US')->getDataValue());
        $this->assertSame('zwart', $item->getItem('labels|nl_BE')->getDataValue());
    }
}
