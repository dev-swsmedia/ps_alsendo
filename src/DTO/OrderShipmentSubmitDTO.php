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

class OrderShipmentSubmitDTO
{
    public string $sender_full_name;
    public string $sender_email;
    public string $sender_street;
    public string $sender_building_number;
    public string $sender_apartment_number;
    public string $sender_postal_code;
    public string $sender_city;
    public string $sender_country;
    public string $sender_phone_number;
    public string $sender_bank_account_number;
    public string $sender_block = '';
    public string $sender_entrance = '';
    public string $sender_floor = '';
    public string $sender_flat = '';
    public string $sender_address_type = 'company';
    public string $sender_company_name = '';
    public string $sender_bank_code = '';
    public string $sender_additional_bank_account_number = '';
    public string $sender_external_id = '';
    public int $shipping_via_pickup_point = 0;
    public int $pickup_request = 0;

    public string $shipping_first_name;
    public string $shipping_last_name;
    public string $shipping_street;
    public string $shipping_building_number;
    public string $shipping_apartment_number;
    public string $shipping_postal_code;
    public string $shipping_city;
    public string $shipping_country;

    public string $shipping_block = '';
    public string $shipping_entrance = '';
    public string $shipping_floor = '';
    public string $shipping_flat = '';

    public ?string $shipping_phone_number = null;
    public ?string $shipping_email = null;

    public float $package_width;
    public float $package_length;
    public float $package_height;
    public float $package_weight;
    public string $package_content;
    public float $package_cod;
    public float $package_declared_value;

    public $shipment_selected_service;
    public string $selected_pickup_type;
    public ?string $shipment_preferred_pickup_date;
    public ?string $shipment_preferred_pickup_hours_from = null;
    public ?string $shipment_preferred_pickup_hours_to = null;
    public ?array $shipment_pickup_point;
    public ?array $merchant_pickup_point;

    public string $package_type_code = '';
    public int $is_nstd = 0;

    public int $order_id;

    public static function fromRequest(array $data): self
    {
        $dto = new self();

        $dto->sender_full_name = $data['sender_full_name'] ?? '';
        $dto->sender_email = $data['sender_email'] ?? '';
        $dto->sender_street = $data['sender_street'] ?? '';
        $dto->sender_building_number = $data['sender_building_number'] ?? '';
        $dto->sender_apartment_number = $data['sender_apartment_number'] ?? '';
        $dto->sender_postal_code = $data['sender_postal_code'] ?? '';
        $dto->sender_city = $data['sender_city'] ?? '';
        $dto->sender_country = $data['sender_country'] ?? '';
        $dto->sender_phone_number = $data['sender_phone_number'] ?? '';
        $dto->sender_bank_account_number = $data['sender_bank_account_number'] ?? '';
        $dto->sender_block = $data['sender_block'] ?? '';
        $dto->sender_entrance = $data['sender_entrance'] ?? '';
        $dto->sender_floor = $data['sender_floor'] ?? '';
        $dto->sender_flat = $data['sender_flat'] ?? '';
        $dto->sender_address_type = $data['sender_address_type'] ?? 'company';
        $dto->sender_company_name = $data['sender_company_name'] ?? '';
        $dto->sender_bank_code = $data['sender_bank_code'] ?? '';
        $dto->sender_additional_bank_account_number = $data['sender_additional_bank_account_number'] ?? '';
        $dto->sender_external_id = $data['sender_external_id'] ?? '';
        $dto->shipping_via_pickup_point = (int) ($data['shipping_via_pickup_point'] ?? 0);
        $dto->pickup_request = (int) ($data['pickup_request'] ?? 0);

        $dto->shipping_first_name = $data['shipping_first_name'] ?? '';
        $dto->shipping_last_name = $data['shipping_last_name'] ?? '';
        $dto->shipping_street = $data['shipping_street'] ?? '';
        $dto->shipping_building_number = $data['shipping_building_number'] ?? '';
        $dto->shipping_apartment_number = $data['shipping_apartment_number'] ?? '';
        $dto->shipping_postal_code = $data['shipping_postal_code'] ?? '';
        $dto->shipping_city = $data['shipping_city'] ?? '';
        $dto->shipping_country = $data['shipping_country'] ?? '';

        $dto->shipping_block = $data['shipping_block'] ?? '';
        $dto->shipping_entrance = $data['shipping_entrance'] ?? '';
        $dto->shipping_floor = $data['shipping_floor'] ?? '';
        $dto->shipping_flat = $data['shipping_flat'] ?? '';

        $dto->shipping_phone_number = $data['shipping_phone_number'] ?? null;
        $dto->shipping_email = $data['shipping_email'] ?? null;

        $dto->package_width = (float) ($data['package_width'] ?? 0);
        $dto->package_length = (float) ($data['package_length'] ?? 0);
        $dto->package_height = (float) ($data['package_height'] ?? 0);
        $dto->package_weight = (float) ($data['package_weight'] ?? 0);
        $dto->package_content = $data['package_content'] ?? '';
        $dto->package_cod = (float) ($data['package_cod'] ?? 0);
        $dto->package_declared_value = (float) ($data['package_declared_value'] ?? 0);

        $dto->shipment_selected_service = isset($data['shipment_selected_service']) && $data['shipment_selected_service'] !== ''
            ? $data['shipment_selected_service']
            : null;
        $dto->selected_pickup_type = $data['selected_pickup_type'] ?? '';
        $dto->shipment_preferred_pickup_date = $data['shipment_preferred_pickup_date'] ?? null;
        $dto->shipment_preferred_pickup_hours_from = $data['shipment_preferred_pickup_hours_from'] ?? null;
        $dto->shipment_preferred_pickup_hours_to = $data['shipment_preferred_pickup_hours_to'] ?? null;
        $dto->shipment_pickup_point = !empty($data['shipment_pickup_point']) ? json_decode($data['shipment_pickup_point'], true) : null;
        $dto->merchant_pickup_point = !empty($data['merchant_pickup_point']) ? json_decode($data['merchant_pickup_point'], true) : null;

        $region = \Configuration::get('ALSENDO_REGION') ?: 'pl';
        $defaultPkgCode = ($region === 'cz') ? 'PACKAGE' : (($region === 'ro') ? 'package' : 'PACZKA');
        $dto->package_type_code = $data['package_shipment_type'] ?? $data['package_shipment_packaging'] ?? $defaultPkgCode;
        $dto->is_nstd = (int) ($data['package_is_nstd'] ?? 0);

        $dto->order_id = (int) ($data['order_id'] ?? 0);

        return $dto;
    }
}
