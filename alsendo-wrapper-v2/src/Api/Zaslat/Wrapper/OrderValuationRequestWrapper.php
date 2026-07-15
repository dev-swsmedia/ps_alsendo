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

use Alsendo\AlsendoWrapper\Api\Zaslat\Model\Money;
use Alsendo\AlsendoWrapper\Api\Zaslat\Model\Order\ZaslatPackage;
use Alsendo\AlsendoWrapper\Api\Zaslat\Model\Rates\RatesGetRequest;
use Alsendo\AlsendoWrapper\Api\Zaslat\Model\Service;
use Alsendo\AlsendoWrapper\Exception\ValidationException;
use Alsendo\AlsendoWrapper\Model\Order\OrderRequest;
use Alsendo\AlsendoWrapper\Model\Shipment;

class OrderValuationRequestWrapper
{
    /**
     * @param OrderRequest $orderRequest
     *
     * @return RatesGetRequest
     *
     * Wraps OrderRequest into RatesGetRequest
     *
     * @throws ValidationException
     */
    public static function wrap(OrderRequest $orderRequest): RatesGetRequest
    {
        if (
            !empty($orderRequest->carrier)
            && strtolower($orderRequest->carrier) === 'gls'
            && !empty($orderRequest->address->receiver->mapPointId)
            && preg_match('/^gls_.{2}-/i', $orderRequest->address->receiver->mapPointId) === 1
        ) {
            $orderRequest->address->receiver->mapPointId = preg_replace(
                '/^gls_.{2}-/i',
                '',
                $orderRequest->address->receiver->mapPointId
            );
        }

        $request = new RatesGetRequest();
        $request->currency = $orderRequest->currency;

        $request->carrier = $orderRequest->carrier;

        if (!empty($orderRequest->pickup->type)) {
            $request->type = $orderRequest->pickup->type;
        }

        if (!empty($orderRequest->pickupRequest) && (bool) $orderRequest->pickupRequest === true) {
            $request->pickupRequest = true;
        }

        if ($orderRequest->pickup->pickupBranch === '1') {
            $request->pickupBranch = '1';
        }

        $request->deliveryBranch = $orderRequest->address->receiver->mapPointId;

        $request->from = ContactWrapper::wrap($orderRequest->address->sender);

        $request->to = ContactWrapper::wrap($orderRequest->address->receiver);

        // Validate that from contact has either id or country
        if (empty($request->from->id) && empty($request->from->country)) {
            throw new ValidationException(['address.sender' => 'Contact from must have either id or country']);
        }

        // Validate that to contact has either id or country
        if (empty($request->to->id) && empty($request->to->country)) {
            throw new ValidationException(['address.receiver' => 'Contact to must have either id or country']);
        }

        if (!empty($orderRequest->cod)) {
            $codService = [
                'code' => 'cod',
                'data' => [
                    'value' => [
                        'value' => $orderRequest->cod->amount,
                        'currency' => $orderRequest->cod->currency ?? $orderRequest->currency,
                    ],
                ],
            ];

            if (!empty($orderRequest->cod->bankaccount)) {
                $codService['data']['bank_account'] = $orderRequest->cod->bankaccount;

                if (!empty($orderRequest->cod->bankCode)) {
                    $codService['data']['bank_code'] = $orderRequest->cod->bankCode;
                }
            }

            $request->services[] = $codService;
        }

        if (!empty($orderRequest->option)) {
            foreach ($orderRequest->option as $option) {
                if (!$option instanceof Service) {
                    throw new ValidationException(['option.service' => 'Option must be instance of Alsendo\AlsendoWrapper\Model\Service']);
                }
                if (strtoupper($option->code) === 'INS') {
                    if (!$option->data instanceof Money) {
                        throw new ValidationException(['option.service.data' => 'Option data must be instance of Alsendo\AlsendoWrapper\Model\Money']);
                    }
                    $request->services[] = [
                        'code' => 'ins',
                        'data' => [
                            'value' => $option->data->value,
                            'currency' => $option->data->currency ?? $orderRequest->currency,
                        ],
                    ];
                }

                if (strtoupper($option->code) === 'DSC') {
                    if (empty($option->data)) {
                        throw new ValidationException(['option.service.data' => 'Option data must be instance of Alsendo\AlsendoWrapper\Model\Money']);
                    }
                    $request->services[] = [
                        'code' => 'dsc',
                        'data' => $option['data'],
                    ];
                }
            }
        }

        foreach ($orderRequest->shipment as $shipment) {
            if (!$shipment instanceof Shipment) {
                throw new ValidationException(['shipment' => 'Shipment must be instance of Alsendo\AlsendoWrapper\Model\Shipment']);
            }
            $zaslatPackage = new ZaslatPackage();
            $zaslatPackage->weight = $shipment->weight;
            $zaslatPackage->length = $shipment->dimension1;
            $zaslatPackage->width = $shipment->dimension2;
            $zaslatPackage->height = $shipment->dimension3;

            $request->packages[] = $zaslatPackage;
        }

        return $request;
    }
}
