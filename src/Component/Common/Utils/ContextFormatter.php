<?php

namespace Misery\Component\Common\Utils;

class ContextFormatter
{
    /**
     * Formats the data array by replacing placeholders with context values.
     *
     * @param array $context The context containing placeholder replacements.
     * @param mixed $data The data to format (can be an array or scalar).
     * @return mixed The formatted data.
     */
    public static function format(array $context, $data): mixed
    {
        $replacements = [];
        foreach ($context as $key => $contextValue) {
            $replacements["%$key%"] = $contextValue;
        }

        return self::recursiveFormat($data, $replacements);
    }

    /**
     * Recursively formats data by replacing placeholders in both keys and values.
     *
     * @param mixed $data The data to format.
     * @param array $replacements The array of replacements.
     * @return mixed The formatted data.
     */
    private static function recursiveFormat($data, array $replacements): mixed
    {
        if (is_array($data)) {
            $newData = [];
            foreach ($data as $key => $value) {
                // Replace placeholders in keys if the key is a string
                $newKey = is_string($key) ? self::replacePlaceholdersInString($key, $replacements) : $key;
                // Recursively format the value
                $newValue = self::recursiveFormat($value, $replacements);
                $newData[$newKey] = $newValue;
            }
            return $newData;
        } elseif (is_string($data)) {
            // Replace the value if it's a string
            return self::replacePlaceholdersInValue($data, $replacements);
        } else {
            // For other data types (int, float, bool, null), return as is
            return $data;
        }
    }

    /**
     * Replaces placeholders in a string with corresponding values.
     *
     * @param string $string The string containing placeholders.
     * @param array $replacements The array of replacements.
     * @return mixed The replaced value.
     */
    private static function replacePlaceholdersInString(string $string, array $replacements): mixed
    {
        if (array_key_exists($string, $replacements)) {
            // If the entire string is a placeholder, return the replacement value directly
            return $replacements[$string];
        } else {
            // Otherwise, replace placeholders within the string
            return strtr($string, array_filter($replacements, 'is_scalar'));
        }
    }

    /**
     * Handles replacement when the value is a string.
     *
     * @param string $value The value containing placeholders.
     * @param array $replacements The array of replacements.
     * @return mixed The replaced value.
     */
    private static function replacePlaceholdersInValue(string $value, array $replacements): mixed
    {
        if (array_key_exists($value, $replacements)) {
            // Return the replacement directly, even if it's an array or object
            return $replacements[$value];
        } else {
            // Replace within the string if it's not an exact match
            return strtr($value, array_filter($replacements, 'is_scalar'));
        }
    }
}
