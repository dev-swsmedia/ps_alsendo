<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\DTO;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/../Services/AddressParser.php';

use Alsendo\Services\AddressParser;

class FullOrderDTO
{
    public ?OrderShipmentSubmitDTO $orderDetails = null;

    public ?array $openCartOrder = null;

    public $orderPickupPoint;

    public static function fromRequestData(OrderShipmentSubmitDTO $submitDTO, array $orderData = [], $pickupPoint = null): self
    {
        $instance = new self();
        $instance->orderDetails = $submitDTO;
        $instance->openCartOrder = $orderData;
        $instance->orderPickupPoint = $pickupPoint;

        return $instance;
    }

    public static function fromPrestaOrder(\Order $order, OrderShipmentSubmitDTO $submitDTO, $pickupPoint = null, array $recipientOverride = null): self
    {
        $address = new \Address($order->id_address_delivery);
        $countryIso = \Country::getIsoById($address->id_country);

        $roComponents = null;
        if (!empty($submitDTO->shipping_street)) {
            $street = $submitDTO->shipping_street;
            $building = $submitDTO->shipping_building_number ?? '';
            $apartment = $submitDTO->shipping_apartment_number ?? '';
        } elseif (!empty($recipientOverride['street'])) {
            $street = $recipientOverride['street'];
            $building = $recipientOverride['building_number'] ?? '';
            $apartment = $recipientOverride['apartment_number'] ?? '';
        } elseif (!empty($recipientOverride['address'])) {
            $parsed = AddressParser::parseAddressToComponents($recipientOverride['address'], $recipientOverride['address2'] ?? '');
            $street = $parsed['street'];
            $building = $parsed['building_number'];
            $apartment = $parsed['apartment_number'];
            if ($countryIso === 'RO') {
                $roSource = $recipientOverride['address2'] ?? '';
                if (empty($roSource)) {
                    $roSource = $apartment;
                }
                $roComponents = AddressParser::parseRomanianComponents($roSource);
                $apartment = '';
            }
        } else {
            $parsed = AddressParser::parseAddressToComponents($address->address1, $address->address2);
            $street = $parsed['street'];
            $building = $parsed['building_number'];
            $apartment = $parsed['apartment_number'];
            if ($countryIso === 'RO') {
                $roSource = $address->address2;
                if (empty($roSource)) {
                    $roSource = $apartment;
                }
                $roComponents = AddressParser::parseRomanianComponents($roSource);
                $apartment = '';
            }
        }

        $composedAddress1 = trim($street . (!empty($building) ? ' ' . $building : '') . (!empty($apartment) ? '/' . $apartment : ''));

        $orderData = [
            'shipping_firstname' => !empty($submitDTO->shipping_first_name) ? $submitDTO->shipping_first_name : (!empty($recipientOverride['first_name']) ? $recipientOverride['first_name'] : $address->firstname),
            'shipping_lastname' => !empty($submitDTO->shipping_last_name) ? $submitDTO->shipping_last_name : (!empty($recipientOverride['last_name']) ? $recipientOverride['last_name'] : $address->lastname),
            'shipping_address_1' => $composedAddress1,
            'shipping_address_2' => '',
            'shipping_street' => $street,
            'shipping_building_number' => $building,
            'shipping_apartment_number' => $apartment,
            'shipping_block' => !empty($submitDTO->shipping_block) ? $submitDTO->shipping_block : ($recipientOverride['block'] ?? ($roComponents['block'] ?? '')),
            'shipping_entrance' => !empty($submitDTO->shipping_entrance) ? $submitDTO->shipping_entrance : ($recipientOverride['entrance'] ?? ($roComponents['entrance'] ?? '')),
            'shipping_floor' => !empty($submitDTO->shipping_floor) ? $submitDTO->shipping_floor : ($recipientOverride['floor'] ?? ($roComponents['floor'] ?? '')),
            'shipping_flat' => !empty($submitDTO->shipping_flat) ? $submitDTO->shipping_flat : ($recipientOverride['flat'] ?? ($roComponents['flat'] ?? '')),
            'shipping_postcode' => !empty($submitDTO->shipping_postal_code) ? $submitDTO->shipping_postal_code : (!empty($recipientOverride['postal_code']) ? $recipientOverride['postal_code'] : $address->postcode),
            'shipping_city' => !empty($submitDTO->shipping_city) ? $submitDTO->shipping_city : (!empty($recipientOverride['city']) ? $recipientOverride['city'] : $address->city),
            'shipping_country' => !empty($submitDTO->shipping_country) ? $submitDTO->shipping_country : (!empty($recipientOverride['country']) ? $recipientOverride['country'] : $countryIso),
            'shipping_company' => !empty($recipientOverride['company']) ? $recipientOverride['company'] : $address->company,
            'shipping_state' => ($address->id_state > 0) ? \State::getNameById($address->id_state) : '',
            'telephone' => !empty($submitDTO->shipping_phone_number) ? $submitDTO->shipping_phone_number : (!empty($recipientOverride['phone']) ? $recipientOverride['phone'] : ($address->phone_mobile ?: $address->phone)),
            'email' => !empty($submitDTO->shipping_email) ? $submitDTO->shipping_email : (!empty($recipientOverride['email']) ? $recipientOverride['email'] : (new \Customer($order->id_customer))->email),
        ];

        return self::fromRequestData($submitDTO, $orderData, $pickupPoint);
    }
}
