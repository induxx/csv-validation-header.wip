<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Converter\Matcher;

class SetValueAction implements ActionInterface, OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'set';

    /** @var array */
    private $options = [
        'key' => null,
        'field' => null,
        'value' => null,
        'allow_creation' => false,
    ];

    public function apply(array $item): array
    {
        $allowCreation = $this->getOption('allow_creation');
        $field = $this->getOption('field', $this->getOption('key'));
        $value = $this->getOption('value');

        $field = $this->findMatchedValueData($item, $field) ?? $field;

        if ($allowCreation) {
            $item[$field] = $value;
            return $item;
        }

        // don't allow array expansion when the field is not found
        // use the expand function for this
        if (key_exists($field, $item)) {
            $item[$field] = $value;
        }

        return $item;
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