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

use Error;

/**
 * Validates object properties against a set of defined rules and collects validation errors.
 *
 * This class allows defining validation rules for object properties using a simple syntax.
 * Rules can be combined with the pipe character ('|') and parameterized using a colon (':').
 * Supported rules include required, integer, string, email, date, time, array, and min/max constraints.
 */
class Validator
{
    private array $rules;

    private array $errors = [];

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Validates the properties of the given object against defined rules.
     *
     * @param object $object The object whose properties are to be validated
     *
     * @return void
     *
     * @throws \RuntimeException If a rule is missing the required parameter (e.g., 'min' or 'max' without a value)
     * @throws \ReflectionException
     */
    public function validate(object $object): void
    {
        $this->errors = [];

        foreach ($this->rules as $propertyPath => $ruleString) {
            $value = $this->getPropertyValue($object, $propertyPath);
            $rules = explode('|', $ruleString);
            $parameter = null;

            if (is_null($value) && !in_array('required', $rules, true)) {
                continue;
            }

            foreach ($rules as $rule) {
                if (strpos($rule, ':') !== false) {
                    [$rule, $parameter] = explode(':', $rule);
                }

                if (null === $parameter && in_array($rule, ['min', 'max'], true)) {
                    throw new \RuntimeException("Rule '{$rule}' needs parameter for '{$propertyPath}'");
                }

                switch ($rule) {
                    case 'required':
                        if (empty($value)) {
                            $this->errors[$propertyPath][] = 'Pole jest wymagane.';
                        }
                        break;
                    case 'integer':
                        if (!is_int($value)) {
                            $this->errors[$propertyPath][] = 'Musi być liczbą całkowitą.';
                        }
                        break;
                    case 'string':
                        if (!is_string($value)) {
                            $this->errors[$propertyPath][] = 'Musi być tekstem.';
                        }
                        break;
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->errors[$propertyPath][] = 'Nieprawidłowy email.';
                        }
                        break;
                    case 'date':
                        if (!strtotime($value)) {
                            $this->errors[$propertyPath][] = 'Nieprawidłowa data.';
                        }
                        break;
                    case 'time':
                        if (!preg_match('/^\d{2}:\d{2}$/', $value)) {
                            $this->errors[$propertyPath][] = 'Nieprawidłowy format czasu (HH:MM).';
                        }
                        break;
                    case 'array':
                        if (!is_array($value)) {
                            $this->errors[$propertyPath][] = 'Musi być tablicą.';
                        }
                        break;
                    case 'min':
                        if ($value < (int) $parameter) {
                            $this->errors[$propertyPath][] = "Wartość musi być większa lub równa $parameter.";
                        }
                        break;
                    case 'min_length':
                        if (strlen($value) < (int) $parameter) {
                            $this->errors[$propertyPath][] = "Wartość musi być większa lub równa $parameter.";
                        }
                        break;
                    case 'max_length':
                        if (strlen($value) > (int) $parameter) {
                            $this->errors[$propertyPath][] = "Wartość musi być mniejsza lub równa $parameter.";
                        }
                        break;
                }
            }
        }
    }

    /**
     * Retrieves the value of a nested property from an object using a dot-separated path.
     *
     * Supports nested property access with array indexing (e.g., "user.profile.age").
     * If any part of the path does not exist or is inaccessible, returns null.
     *
     * @param object $object The object from which to retrieve the property value
     * @param string $propertyPath The dot-separated path to the desired property (e.g., "user.profile.age")
     *
     * @return mixed The value of the property if found, null otherwise
     *
     * @throws \ReflectionException If a property cannot be accessed via reflection
     */
    private function getPropertyValue(object $object, string $propertyPath)
    {
        $properties = explode('.', str_replace(']', '', str_replace('[', '.', $propertyPath)));
        $current = $object;

        foreach ($properties as $property) {
            if (is_array($current)) {
                if (!isset($current[$property])) {
                    return null;
                }
                $current = $current[$property];
            } elseif (is_object($current)) {
                if (!property_exists($current, $property)) {
                    return null;
                }
                $reflection = new \ReflectionClass($current);
                $prop = $reflection->getProperty($property);
                $prop->setAccessible(true);
                try {
                    $current = $prop->getValue($current);
                } catch (\Error $e) {
                    return null;
                }
            } else {
                return null;
            }
        }

        return $current;
    }

    /**
     * Checks whether any validation errors have been recorded during the validation process.
     *
     * @return bool True if there are validation errors, false otherwise
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Returns the array of validation errors collected during the validation process.
     *
     * @return array The validation errors, keyed by a property path, with error messages as values
     *
     * @throws \RuntimeException If validation errors were encountered during the validation process
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
