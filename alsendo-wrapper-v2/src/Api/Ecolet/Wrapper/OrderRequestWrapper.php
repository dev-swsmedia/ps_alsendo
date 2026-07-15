<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Ecolet\Wrapper;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Ecolet\ApiEcoletClient;
use Alsendo\AlsendoWrapper\Api\Ecolet\Model\AdditionalService;
use Alsendo\AlsendoWrapper\Api\Ecolet\Model\Dimension;
use Alsendo\AlsendoWrapper\Api\Ecolet\Model\Order\Courier;
use Alsendo\AlsendoWrapper\Api\Ecolet\Model\Order\CourierPickup;
use Alsendo\AlsendoWrapper\Api\Ecolet\Model\Order\EcoletOrderRequest;
use Alsendo\AlsendoWrapper\Api\Ecolet\Model\Parcel;
use Alsendo\AlsendoWrapper\Model\Order\OrderRequest;
use Alsendo\AlsendoWrapper\Model\Shipment;

class OrderRequestWrapper
{
    private const COUNTY_COUNTRIES = ['RO', 'BG'];

    public static function wrap(OrderRequest $orderRequest, ApiEcoletClient $apiClient): EcoletOrderRequest
    {
        $ecoletOrderRequest = new EcoletOrderRequest();
        $ecoletOrderRequest->sender = $orderRequest->address->sender;
        $ecoletOrderRequest->sender->country = strtolower($ecoletOrderRequest->sender->countryCode);
        $ecoletOrderRequest->sender->county = $orderRequest->address->sender->stateCode;
        $ecoletOrderRequest->sender->locality = $orderRequest->address->sender->city;

        if ($ecoletOrderRequest->sender->localityId === null) {
            $senderCountry = strtoupper($orderRequest->address->sender->countryCode);
            if (in_array($senderCountry, self::COUNTY_COUNTRIES)) {
                $localityData = $apiClient->getLocalityIdByCity(
                    $orderRequest->address->sender->countryCode,
                    $orderRequest->address->sender->city
                );
                $ecoletOrderRequest->sender->localityId = $localityData['id'];
                if (!empty($localityData['county'])) {
                    $ecoletOrderRequest->sender->county = $localityData['county'];
                }
            }
        }

        $senderStreet = self::parseStreetFromLines(
            $orderRequest->address->sender->line1 ?? '',
            $orderRequest->address->sender->line2 ?? ''
        );
        if ($ecoletOrderRequest->sender->streetName === null) {
            $ecoletOrderRequest->sender->streetName = $senderStreet['streetName'];
        }
        if ($ecoletOrderRequest->sender->streetNumber === null) {
            $ecoletOrderRequest->sender->streetNumber = $senderStreet['streetNumber'];
        }

        unset(
            $ecoletOrderRequest->sender->countryCode,
            $ecoletOrderRequest->sender->line1,
            $ecoletOrderRequest->sender->line2,
            $ecoletOrderRequest->sender->stateCode,
            $ecoletOrderRequest->sender->isResidential,
            $ecoletOrderRequest->sender->foreignAddressId,
            $ecoletOrderRequest->sender->city,
            $ecoletOrderRequest->sender->id,
            $ecoletOrderRequest->sender->mapPointName,
        );

        $ecoletOrderRequest->receiver = $orderRequest->address->receiver;
        $ecoletOrderRequest->receiver->country = strtolower($ecoletOrderRequest->receiver->countryCode);
        $ecoletOrderRequest->receiver->county = $orderRequest->address->receiver->stateCode;
        $ecoletOrderRequest->receiver->locality = $orderRequest->address->receiver->city;

        if ($ecoletOrderRequest->receiver->localityId === null) {
            $receiverCountry = strtoupper($orderRequest->address->receiver->countryCode);
            if (in_array($receiverCountry, self::COUNTY_COUNTRIES)) {
                $localityData = $apiClient->getLocalityIdByCity(
                    $orderRequest->address->receiver->countryCode,
                    $orderRequest->address->receiver->city
                );
                $ecoletOrderRequest->receiver->localityId = $localityData['id'];
                if (!empty($localityData['county'])) {
                    $ecoletOrderRequest->receiver->county = $localityData['county'];
                }
            }
        }

        $receiverStreet = self::parseStreetFromLines(
            $orderRequest->address->receiver->line1 ?? '',
            $orderRequest->address->receiver->line2 ?? ''
        );
        if ($ecoletOrderRequest->receiver->streetName === null) {
            $ecoletOrderRequest->receiver->streetName = $receiverStreet['streetName'];
        }
        if ($ecoletOrderRequest->receiver->streetNumber === null) {
            $ecoletOrderRequest->receiver->streetNumber = $receiverStreet['streetNumber'];
        }

        if ($orderRequest->address->receiver->mapPointId !== null) {
            $ecoletOrderRequest->receiver->hasMapPoint = true;
        }

        unset(
            $ecoletOrderRequest->receiver->countryCode,
            $ecoletOrderRequest->receiver->line1,
            $ecoletOrderRequest->receiver->line2,
            $ecoletOrderRequest->receiver->stateCode,
            $ecoletOrderRequest->receiver->isResidential,
            $ecoletOrderRequest->receiver->foreignAddressId,
            $ecoletOrderRequest->receiver->city,
            $ecoletOrderRequest->receiver->id,
            $ecoletOrderRequest->receiver->mapPointName,
        );

        $parcel = new Parcel();
        /** @var Shipment $shipment */
        $shipment = $orderRequest->shipment[0];

        $parcel->type = $shipment->shipmentTypeCode;
        $parcel->observations = $shipment->observations;
        $parcel->shape = $shipment->shape;
        $parcel->amount = count($orderRequest->shipment);

        unset(
            $parcel->weight,
            $parcel->dimensions,
            $parcel->declaredValue,
            $parcel->content,
        );

        $ecoletOrderRequest->parcel = $parcel;

        foreach ($orderRequest->shipment as $shipment) {
            $parcel = new Parcel();
            $parcel->weight = $shipment->weight;
            $parcel->amount = $shipment->amount;
            $parcel->type = $shipment->shipmentTypeCode;
            $parcel->content = $shipment->content ?? $orderRequest->content;
            $parcel->declaredValue = $shipment->declaredValue;
            $parcel->dimensions = new Dimension(
                $shipment->dimension1,
                $shipment->dimension2,
                $shipment->dimension3
            );
            $parcel->observations = $shipment->observations;
            $parcel->shape = $shipment->shape;
            unset(
                $parcel->shape,
                $parcel->observations,
                $parcel->amount,
            );

            $ecoletOrderRequest->parcels[] = $parcel;
        }

        $ecoletOrderRequest->additionalServices = $orderRequest->additionalServices;

        if ($orderRequest->cod) {
            $ecoletOrderRequest->additionalServices['cod'] = new AdditionalService(true, $orderRequest->cod->amount);
        } else {
            $ecoletOrderRequest->additionalServices['cod'] = new AdditionalService(false, '0');
        }

        $courier = new Courier();
        if (!empty($orderRequest->serviceId)) {
            $courier->service = $orderRequest->serviceId;
        }
        $courier->pickup = new CourierPickup();
        $courier->pickup->type = $orderRequest->pickup->type;
        $courier->pickup->date = $orderRequest->pickup->date;
        $courier->pickup->time = $orderRequest->pickup->hoursFrom;

        $ecoletOrderRequest->courier = $courier;

        $ecoletOrderRequest->source = $orderRequest->source;

        // Pickup point: wyczyść pola adresowe — Ecolet wymaga czystych
        // danych gdy ma map_point_id (wymagane: name, country, map_point_id,
        // phone, email, locality_id — reszta pól adresowych czysta)
        if ($ecoletOrderRequest->receiver->hasMapPoint === true) {
            $ecoletOrderRequest->receiver->streetName = '';
            $ecoletOrderRequest->receiver->streetNumber = '';
            $ecoletOrderRequest->receiver->block = '';
            $ecoletOrderRequest->receiver->entrance = '';
            $ecoletOrderRequest->receiver->floor = '';
            $ecoletOrderRequest->receiver->flat = '';
            $ecoletOrderRequest->receiver->mapPointId = (int) $ecoletOrderRequest->receiver->mapPointId;
            unset($ecoletOrderRequest->receiver->company);
        }

        if ($ecoletOrderRequest->sender->hasMapPoint === true) {
            $ecoletOrderRequest->sender->streetName = '';
            $ecoletOrderRequest->sender->streetNumber = '';
            $ecoletOrderRequest->sender->block = '';
            $ecoletOrderRequest->sender->entrance = '';
            $ecoletOrderRequest->sender->floor = '';
            $ecoletOrderRequest->sender->flat = '';
            $ecoletOrderRequest->sender->mapPointId = (int) $ecoletOrderRequest->sender->mapPointId;
            unset($ecoletOrderRequest->sender->company);
        }

        // Ecolet API nie akceptuje null w polach adresowych
        foreach (['sender', 'receiver'] as $role) {
            $contact = $ecoletOrderRequest->$role;
            $contact->block = $contact->block ?? '';
            $contact->entrance = $contact->entrance ?? '';
            $contact->floor = $contact->floor ?? '';
            $contact->flat = $contact->flat ?? '';
            $contact->streetNumber = $contact->streetNumber ?? '';
        }

        // declared_value null→0
        foreach ($ecoletOrderRequest->parcels as $parcel) {
            if ($parcel->declaredValue === null) {
                $parcel->declaredValue = 0;
            }
        }

        return $ecoletOrderRequest;
    }

    private static function parseStreetFromLines(string $line1, string $line2): array
    {
        $line1 = trim($line1);
        $line2 = trim($line2);

        if (preg_match('/^(.*?)\s+(\d+\w*)\s*$/u', $line1, $m)) {
            return [
                'streetName' => trim($m[1]),
                'streetNumber' => $m[2] . ($line2 !== '' ? ' ' . $line2 : ''),
            ];
        }

        return [
            'streetName' => $line1,
            'streetNumber' => $line2,
        ];
    }
}
