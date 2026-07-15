<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Zaslat\Wrapper;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Ecolet\Model\AdditionalService;
use Alsendo\AlsendoWrapper\Api\Zaslat\Model\Order\ZaslatOrderRequest;
use Alsendo\AlsendoWrapper\Api\Zaslat\Model\Order\ZaslatPackage;
use Alsendo\AlsendoWrapper\Api\Zaslat\Model\Order\ZaslatService;
use Alsendo\AlsendoWrapper\Api\Zaslat\Model\Order\ZaslatServiceData;
use Alsendo\AlsendoWrapper\Api\Zaslat\Model\Order\ZaslatShipment;
use Alsendo\AlsendoWrapper\Model\Order\OrderRequest;
use Alsendo\AlsendoWrapper\Model\Shipment;

class OrderRequestWrapper
{
    private const DROPOFF_ONLY_CARRIERS = [
        'BALIKOVNA',
        'ZASILKOVNA',
    ];

    /**
     * @param OrderRequest $orderRequest
     *
     * @return ZaslatOrderRequest
     *
     * Wraps OrderRequest into ZaslatOrderRequest
     *
     * @throws \DateMalformedStringException|\Exception
     */
    public static function wrap(OrderRequest $orderRequest): ZaslatOrderRequest
    {
        $zaslatOrderRequest = new ZaslatOrderRequest();
        $zaslatOrderRequest->currency = $orderRequest->currency;
        $zaslatOrderRequest->paymentType = $orderRequest->paymentType;
        $zaslatOrderRequest->voucher = $orderRequest->promoCode;

        $isPickupBranch = false;
        if (isset($orderRequest->pickup->pickupBranch) && (bool) $orderRequest->pickup->pickupBranch === true) {
            $zaslatOrderRequest->pickupBranch = true;
            $isPickupBranch = true;
        }

        $zaslatShipment = new ZaslatShipment();

        $carrierUpper = !empty($orderRequest->carrier) ? strtoupper($orderRequest->carrier) : '';
        $isDropoffOnlyCarrier = in_array($carrierUpper, self::DROPOFF_ONLY_CARRIERS, true);

        if (!$isDropoffOnlyCarrier && !empty($orderRequest->pickup->type)) {
            $zaslatShipment->type = $orderRequest->pickup->type;
        }

        $zaslatShipment->carrier = $orderRequest->carrier;

        if (!$isDropoffOnlyCarrier && !is_null($orderRequest->pickupRequest)) {
            $zaslatShipment->pickupRequest = true;
        }

        if (!$isDropoffOnlyCarrier && !empty($orderRequest->pickup->date)) {
            $zaslatShipment->pickupDate = (new \DateTimeImmutable($orderRequest->pickup->date))->format('Y-m-d');
        }

        $zaslatShipment->from = ContactWrapper::wrap($orderRequest->address->sender);
        $zaslatShipment->to = ContactWrapper::wrap($orderRequest->address->receiver);

        $zaslatShipment->note = $orderRequest->comment;

        if ($isDropoffOnlyCarrier) {
            $zaslatShipment->pickupBranch = '1';
        }

        $zaslatShipment->deliveryBranch = $orderRequest->address->receiver->mapPointId;

        foreach ($orderRequest->shipment as $shipment) {
            /** @var Shipment $shipment */
            $zaslatPackage = new ZaslatPackage();
            $zaslatPackage->weight = $shipment->weight;
            $zaslatPackage->length = $shipment->dimension1;
            $zaslatPackage->width = $shipment->dimension2;
            $zaslatPackage->height = $shipment->dimension3;

            $zaslatShipment->packages[] = $zaslatPackage;
        }

        if ($orderRequest->additionalServices) {
            foreach ($orderRequest->additionalServices as $key => $additionalService) {
                /** @var AdditionalService $additionalService */
                $zaslatSerivce = new ZaslatService();
                $zaslatSerivce->code = $key;
                $zaslatSerivce->data = new ZaslatServiceData();
                $zaslatSerivce->data->currency = $orderRequest->currency;
                $zaslatSerivce->data->value = (int) $additionalService->amount;

                $zaslatShipment->services[] = $zaslatSerivce;
            }
        }

        if (!empty($orderRequest->cod)) {
            $codService = [
                'code' => 'cod',
                'data' => [
                    'bank_account' => $orderRequest->cod->bankaccount,
                    'value' => [
                        'value' => $orderRequest->cod->amount,
                        'currency' => $orderRequest->cod->currency ?? $orderRequest->currency,
                    ],
                ],
            ];

            if (isset($orderRequest->cod->bankCode) && !empty($orderRequest->cod->bankCode)) {
                $codService['data']['bank_code'] = $orderRequest->cod->bankCode;
            }

            $zaslatShipment->services[] = $codService;
        }

        $zaslatShipment->reference = $orderRequest->reference;

        if ($isPickupBranch) {
            $zaslatShipment->pickupBranch = '1';
        }

        $zaslatOrderRequest->shipments[] = $zaslatShipment;

        return $zaslatOrderRequest;
    }
}
