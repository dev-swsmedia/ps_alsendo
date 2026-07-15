<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class Request implements RequestInterface
{
    /**
     * Konwertuje obiekt na tablicę.
     *
     * @param array $exclude Lista pól do pominięcia
     * @param bool $convertToSnakeCase Czy zmieniać nazwy zmiennych na snake_case
     *
     * @return array
     */
    public function toArray(array $exclude = [], bool $convertToSnakeCase = true): array
    {
        $data = get_object_vars($this); // Downloading the property of the object as a board
        $result = [];

        foreach ($data as $key => $value) {
            // Conversion to snake_case, if required
            $formattedKey = $convertToSnakeCase ? $this->camelToSnake($key) : $key;

            if (in_array($formattedKey, $exclude, true)) {
                continue; // Skipping excluded fields
            }

            // Value conversion (recursive)
            $result[$formattedKey] = $this->normalizeValue($value, $exclude, $convertToSnakeCase);
        }

        return $result;
    }

    /**
     * Konwertuje nazwę zmiennej z CamelCase na snake_case.
     *
     * @param string $input
     *
     * @return string
     */
    private function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * Normalizuje wartość do postaci tablicowej (obsługuje obiekty i tablice obiektów).
     *
     * @param mixed $value
     * @param array $exclude
     * @param bool $convertToSnakeCase
     *
     * @return mixed
     */
    private function normalizeValue($value, array $exclude, bool $convertToSnakeCase)
    {
        if (is_array($value)) {
            // Jeśli to tablica, przetwarzamy każdy element
            return array_map(fn ($item) => $this->normalizeValue($item, $exclude, $convertToSnakeCase), $value);
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            // Jeśli to obiekt z metodą toArray(), wywołujemy ją
            return $value->toArray($exclude, $convertToSnakeCase);
        }

        return $value; // Jeśli to prosty typ, zwracamy wartość bez zmian
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        } else {
            $this->throwNonExistent($property);
        }
    }

    public function __isset($property)
    {
        return property_exists($this, $property);
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        $this->throwNonExistent($property);
    }

    protected function throwNonExistent($property): void
    {
        $class = get_class($this);
        throw new \RuntimeException("Property not existing {$property} in {$class}");
    }
}
