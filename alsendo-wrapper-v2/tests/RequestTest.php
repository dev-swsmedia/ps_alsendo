<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests;

use Alsendo\AlsendoWrapper\Model\Order\OrderRequest;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RequestTest extends TestCase
{
    public function testToArray()
    {
        $request = new OrderRequest();
        $request->serviceId = 'bar';
        $request->shipmentValue = 100;

        $expected = [
            'service_id' => 'bar',
            'shipment_value' => 100,
            'cod' => null,
            'additional_services' => null,
            'comment' => null,
            'content' => null,

            'currency' => null,
            'payment_type' => null,
            'carrier' => null,
            'reference' => null,
            'promo_code' => null,
            'shipment_currency' => null,
            'pickup_request' => null,
        ];

        $this->assertEquals($expected, $request->toArray());
    }

    public function testToArrayWithExclude()
    {
        $request = new OrderRequest();
        $request->serviceId = 'bar';
        $request->shipmentValue = 100;

        $expected = [
            'service_id' => 'bar',
            'cod' => null,
            'additional_services' => null,
            'comment' => null,
            'content' => null,

            'currency' => null,
            'payment_type' => null,
            'carrier' => null,
            'reference' => null,
            'promo_code' => null,
            'shipment_currency' => null,
            'pickup_request' => null,
        ];

        $this->assertEquals($expected, $request->toArray(['shipment_value']));
    }

    public function testToArrayWithSnakeCase()
    {
        $request = new OrderRequest();
        $request->serviceId = 'bar';

        $expected = [
            'service_id' => 'bar',
            'cod' => null,
            'additional_services' => null,
            'comment' => null,
            'content' => null,

            'currency' => null,
            'payment_type' => null,
            'carrier' => null,
            'reference' => null,
            'promo_code' => null,
            'shipment_currency' => null,
            'pickup_request' => null,
        ];

        $this->assertEquals($expected, $request->toArray([], true));
    }

    public function testNormalizeValue()
    {
        $request = new OrderRequest();
        $request->option = ['bar', 'baz'];

        $expected = [
            'service_id' => null,
            'option' => [
                'bar',
                'baz',
            ],
            'cod' => null,
            'additional_services' => null,
            'comment' => null,
            'content' => null,

            'currency' => null,
            'payment_type' => null,
            'carrier' => null,
            'reference' => null,
            'promo_code' => null,
            'shipment_currency' => null,
            'pickup_request' => null,
        ];

        $this->assertEquals($expected, $request->toArray());
    }
}
