<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\Services;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/PickupHoursService.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/BankAccountValidator.php';

class OrderValidator
{
    public function validateShipmentData($dto)
    {
        $errors = [];

        if (empty($dto->sender_full_name)) {
            $errors['sender_full_name'] = 'Sender full name is required';
        }

        if (empty($dto->sender_street)) {
            $errors['sender_street'] = 'Sender street is required';
        }

        if (empty($dto->sender_postal_code)) {
            $errors['sender_postal_code'] = 'Sender postal code is required';
        }

        if (empty($dto->sender_city)) {
            $errors['sender_city'] = 'Sender city is required';
        }

        if (empty($dto->sender_country) || strlen($dto->sender_country) !== 2) {
            $errors['sender_country'] = 'Valid sender country code is required';
        }

        if (empty($dto->sender_phone_number)) {
            $errors['sender_phone_number'] = 'Valid sender phone number is required';
        }

        if (empty($dto->sender_email) || !filter_var($dto->sender_email, FILTER_VALIDATE_EMAIL)) {
            $errors['sender_email'] = 'Valid sender email is required';
        }

        if (empty($dto->shipping_first_name)) {
            $errors['shipping_first_name'] = 'Recipient first name is required';
        }
        if (empty($dto->shipping_last_name)) {
            $errors['shipping_last_name'] = 'Recipient last name is required';
        }
        if (empty($dto->shipping_street)) {
            $errors['shipping_street'] = 'Recipient street is required';
        }
        if (empty($dto->shipping_building_number)) {
            $errors['shipping_building_number'] = 'Recipient building number is required';
        }
        if (empty($dto->shipping_postal_code)) {
            $errors['shipping_postal_code'] = 'Recipient postal code is required';
        }
        if (empty($dto->shipping_city)) {
            $errors['shipping_city'] = 'Recipient city is required';
        }
        if (empty($dto->shipping_country) || strlen($dto->shipping_country) !== 2) {
            $errors['shipping_country'] = 'Valid recipient country code is required';
        }
        if (empty($dto->shipping_phone_number)) {
            $errors['shipping_phone_number'] = 'Recipient phone number is required';
        }
        if (empty($dto->shipping_email) || !filter_var($dto->shipping_email, FILTER_VALIDATE_EMAIL)) {
            $errors['shipping_email'] = 'Valid recipient email is required';
        }

        $codAmount = isset($dto->package_cod) ? (float) $dto->package_cod : 0;
        if ($codAmount > 0) {
            $region = \Configuration::get('ALSENDO_REGION') ?: 'pl';
            $bankAccount = isset($dto->sender_bank_account_number) ? trim($dto->sender_bank_account_number) : '';

            if (empty($bankAccount)) {
                $errors['sender_bank_account_number'] = 'Bank account number is required for COD';
            } elseif ($region === 'cz') {
                if (strpos($bankAccount, '/') !== false) {
                    if (!BankAccountValidator::isValidCzechLocal($bankAccount)) {
                        $errors['sender_bank_account_number'] = 'Invalid Czech bank account format (e.g. 123456-1234567890/0800)';
                    }
                } else {
                    if (!BankAccountValidator::isValidCzechAccountPart($bankAccount)) {
                        $errors['sender_bank_account_number'] = 'Invalid Czech bank account number (e.g. 1234567890 or 123456-1234567890)';
                    }
                    $bankCode = isset($dto->sender_bank_code) ? trim($dto->sender_bank_code) : '';
                    if (empty($bankCode)) {
                        $errors['sender_bank_code'] = 'Bank code is required for COD (e.g. 0100)';
                    } elseif (!BankAccountValidator::isValidCzechBankCode($bankCode)) {
                        $errors['sender_bank_code'] = 'Bank code must be 4 digits (e.g. 0100)';
                    }
                }
            } elseif (!BankAccountValidator::isValid($bankAccount, $dto->sender_country ?? '')) {
                $errors['sender_bank_account_number'] = 'Invalid bank account format';
            }

            if ($region === 'cz') {
                $iban = isset($dto->sender_additional_bank_account_number) ? trim($dto->sender_additional_bank_account_number) : '';
                if (!empty($iban) && !BankAccountValidator::isValidIban($iban)) {
                    $errors['sender_additional_bank_account_number'] = 'Invalid IBAN format';
                }
            }
        }

        if (empty($dto->package_width) || $dto->package_width <= 0) {
            $errors['package_width'] = 'Package width must be greater than 0';
        }

        if (empty($dto->package_length) || $dto->package_length <= 0) {
            $errors['package_length'] = 'Package length must be greater than 0';
        }

        if (empty($dto->package_height) || $dto->package_height <= 0) {
            $errors['package_height'] = 'Package height must be greater than 0';
        }

        if (empty($dto->package_weight) || $dto->package_weight <= 0) {
            $errors['package_weight'] = 'Package weight must be greater than 0';
        }

        if (empty($dto->shipment_selected_service)) {
            $errors['shipment_selected_service'] = 'Shipping service must be selected';
        }

        if ($dto->selected_pickup_type === 'SELF' || $dto->selected_pickup_type === 'DROP_OFF_POINT') {
            if (empty($dto->shipment_pickup_point)) {
                $errors['shipment_pickup_point'] = 'Pickup point must be selected';
            }
        }

        if ($dto->selected_pickup_type === 'COURIER') {
            if (empty($dto->shipment_preferred_pickup_date)) {
                $errors['shipment_preferred_pickup_date'] = 'Pickup date is required for courier pickup';
            } else {
                if (PickupHoursService::isDateInPast($dto->shipment_preferred_pickup_date)) {
                    $errors['shipment_preferred_pickup_date'] = 'Pickup date cannot be in the past. Please select today or a future date.';
                }
            }

            $hoursFrom = $dto->shipment_preferred_pickup_hours_from ?? null;
            $hoursTo = $dto->shipment_preferred_pickup_hours_to ?? null;

            if (empty($hoursFrom)) {
                $errors['shipment_preferred_pickup_hours_from'] = 'Pickup start time is required for courier pickup';
            }
            if (empty($hoursTo)) {
                $errors['shipment_preferred_pickup_hours_to'] = 'Pickup end time is required for courier pickup';
            }

            if (!empty($hoursFrom) && !empty($hoursTo)) {
                $minHour = '08:00';
                $maxHour = '17:00';
                $minWindowMinutes = 120;

                if (strtotime($hoursFrom) < strtotime($minHour)) {
                    $errors['shipment_preferred_pickup_hours_from'] = 'Pickup time cannot be earlier than 08:00';
                }

                if (strtotime($hoursTo) > strtotime($maxHour)) {
                    $errors['shipment_preferred_pickup_hours_to'] = 'Pickup time cannot be later than 17:00 due to courier operational hours';
                }

                $fromMinutes = $this->timeToMinutes($hoursFrom);
                $toMinutes = $this->timeToMinutes($hoursTo);

                if ($toMinutes <= $fromMinutes) {
                    $errors['shipment_preferred_pickup_hours_to'] = 'Pickup end time must be after start time';
                } elseif (($toMinutes - $fromMinutes) < $minWindowMinutes) {
                    $errors['shipment_preferred_pickup_hours_from'] = 'Minimum pickup time window is 2 hours to ensure courier availability';
                }

                if (!empty($dto->shipment_preferred_pickup_date)
                    && !isset($errors['shipment_preferred_pickup_date'])) {
                    $pickupDate = $dto->shipment_preferred_pickup_date;

                    if ($pickupDate === date('Y-m-d')) {
                        $currentTime = date('H:i');
                        if ($hoursTo <= $currentTime) {
                            $errors['shipment_preferred_pickup_hours_to'] = 'Pickup end time has already passed. Please select a future time or choose a later date.';
                        }
                    }
                }
            }
        }

        return $errors;
    }

    private function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);

        return ($hours * 60) + $minutes;
    }
}
