<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class to map JSON data to objects with support for nested structures and type conversion.
 */
class Json
{
    public const DATE_TIME = 'datetime';
    public const INTEGER = 'integer';
    public const FLOAT = 'float';
    public const BOOLEAN = 'boolean';

    public static function mapArrayToObject(array $data, $class, $reflection = null, string $parentKey = null)
    {
        if (is_object($class) && $reflection !== null) {
            $object = $class;
        } else {
            if (!class_exists($class)) {
                throw new \RuntimeException("Klasa $class nie istnieje!");
            }

            $reflection = new \ReflectionClass($class);
            $object = $reflection->newInstance();
        }

        $propertyMap = [];
        if (is_string($class) && method_exists($class, 'getPropertyTypeMap')) {
            $propertyMap = call_user_func([$class, 'getPropertyTypeMap']);
        }

        foreach ($data as $jsonKey => $value) {
            if (is_string($parentKey)) {
                $jsonKey = $parentKey . '.' . $jsonKey;
            }
            $mapping = $propertyMap[$jsonKey] ?? [];

            if (empty($mapping) && is_array($value)) {
                self::mapArrayToObject($value, $object, $reflection, $jsonKey);
            }
            $mappedField = $mapping['mappedTo'] ?? $jsonKey;
            $propertyClass = $mapping['type'] ?? null;
            $fieldMapping = $mapping['mapFields'] ?? [];
            $convertType = $mapping['convert'] ?? null;

            // Obsługa `shipments.0.nazwa_pola`
            if (preg_match('/(.+?)\.(\d+)\.(.+)/', $mappedField, $matches)) {
                [$fullMatch, $arrayField, $index, $field] = $matches;

                if (!property_exists($object, $arrayField)) {
                    continue;
                }

                if (!isset($object->$arrayField[$index])) {
                    $object->$arrayField[$index] = new $propertyClass();
                }

                if (!empty($fieldMapping) && isset($fieldMapping[$jsonKey])) {
                    $field = $fieldMapping[$jsonKey];
                }

                if (property_exists($object->$arrayField[$index], $field)) {
                    $object->$arrayField[$index]->$field = self::convert($value, $convertType);
                }
                continue;
            }

            if (!$reflection->hasProperty($mappedField)) {
                continue;
            }

            $propertyType = $reflection->getProperty($mappedField)->getType();
            $propertyTypeName = $propertyType ? $propertyType->getName() : null;

            // Obsługa podstawowych typów (int, float, boolean, datetime)
            if (in_array($propertyTypeName, ['string', 'int', 'float', 'bool', 'DateTimeImmutable', null, true, false], true) && !$propertyClass) {
                $object->$mappedField = self::convert($value, $convertType);
                continue;
            }

            // Mapowanie obiektów i ich pól
            if ($propertyClass && class_exists($propertyClass) && is_array($value)) {
                $nestedPropertyMap = method_exists($propertyClass, 'getPropertyTypeMap')
                    ? $propertyClass::getPropertyTypeMap()
                    : $propertyMap[$jsonKey];

                if ($propertyTypeName === 'array') {
                    $arrayData = is_array(reset($value)) ? $value : [$value];
                    $existingArray = $object->$mappedField ?? [];

                    $object->$mappedField = array_map(
                        function ($item, $index) use ($propertyClass, $mapping, $nestedPropertyMap, $existingArray) {
                            // Sprawdź czy w istniejącej tablicy jest już obiekt na tej pozycji
                            $existingObject = isset($existingArray[$index]) && ($existingArray[$index] instanceof $propertyClass)
                                ? $existingArray[$index]
                                : new $propertyClass();

                            return self::mapArrayToObjectWithFieldMapping($item, $existingObject, $mapping['mapFields'] ?? $nestedPropertyMap);
                        },
                        $arrayData,
                        array_keys($arrayData)
                    );
                    continue;
                }

                $object->$mappedField = self::mapArrayToObjectWithFieldMapping($value, new $propertyClass(), $mapping['mapFields'] ?? $nestedPropertyMap);
                continue;
            }

            // Obsługa `mapFields`
            if (isset($mapping['mapFields']) && property_exists($object, $mappedField) && is_string($mapping['mapFields'])) {
                $object->$mappedField = self::mapFieldToObject($object->$mappedField, $propertyClass, $mapping['mapFields'], $value, $convertType);
                continue;
            }

            $object->$mappedField = self::convert($value, null);
        }

        return $object;
    }

    private static function mapFieldToObject($object, string $className, string $mapField, $value, $convertType = null)
    {
        if (!class_exists($className)) {
            throw new \RuntimeException("Klasa $className nie istnieje!");
        }

        if (!$object instanceof $className) {
            $reflection = new \ReflectionClass($className);
            $object = $reflection->newInstance();
        }

        if (property_exists($object, $mapField)) {
            $object->$mapField = self::convert($value, $convertType);
        }

        return $object;
    }

    private static function mapArrayToObjectWithFieldMapping(array $data, object $object, array $fieldMapping, string $parentKey = null): object
    {
        foreach ($data as $jsonKey => $value) {
            if (is_string($parentKey)) {
                $jsonKey = $parentKey . '.' . $jsonKey;
            }
            $mapping = $fieldMapping[$jsonKey] ?? [];

            if (empty($mapping) && is_array($value)) {
                self::mapArrayToObjectWithFieldMapping($value, $object, $fieldMapping, $jsonKey);
            }
            $mappedField = is_array($mapping) ? ($mapping['mappedTo'] ?? $jsonKey) : $mapping;
            $convertType = is_array($mapping) ? ($mapping['convert'] ?? null) : null;
            $propertyClass = is_array($mapping) ? ($mapping['type'] ?? null) : null;

            if (property_exists($object, $mappedField)) {
                if ($propertyClass && class_exists($propertyClass) && is_array($value)) {
                    // Sprawdzenie czy pole już zawiera instancję odpowiedniej klasy
                    $existingValue = $object->$mappedField;

                    if (!($existingValue instanceof $propertyClass)) {
                        $existingValue = new $propertyClass();
                    }

                    // Obsługa zagnieżdżonych klas
                    $nestedPropertyMap = method_exists($propertyClass, 'getPropertyTypeMap')
                        ? $propertyClass::getPropertyTypeMap()
                        : $fieldMapping;

                    $object->$mappedField = self::mapArrayToObjectWithFieldMapping($value, $existingValue, $nestedPropertyMap);
                } elseif (is_string($object->$mappedField) && $object->$mappedField !== '') {
                    $object->$mappedField .= ' ' . $value;
                } else {
                    $object->$mappedField = self::convert($value, $convertType);
                }
            }
        }

        return $object;
    }

    private static function convert($value, ?string $type)
    {
        switch ($type) {
            case self::DATE_TIME:
                return is_string($value) ? new \DateTimeImmutable($value) : null;
            case self::INTEGER:
                return is_numeric($value) ? (int) $value : null;
            case self::FLOAT:
                return is_numeric($value) ? (float) $value : null;
            case self::BOOLEAN:
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            default:
                return $value;
        }
    }

    public static function mapJsonToObject(string $json, string $class)
    {
        return self::mapArrayToObject($json ? json_decode($json, true, 512, JSON_THROW_ON_ERROR) : [], $class);
    }
}
