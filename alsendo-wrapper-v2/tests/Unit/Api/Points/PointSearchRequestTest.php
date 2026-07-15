<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Points;

use Alsendo\AlsendoWrapper\Api\Points\Model\Operator;
use Alsendo\AlsendoWrapper\Api\Points\Model\PointField;
use Alsendo\AlsendoWrapper\Api\Points\Model\PointSearchRequest;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PointSearchRequestTest extends TestCase
{
    public function testToQueryParamsBasic(): void
    {
        $request = new PointSearchRequest();
        $request->searchText = 'Kraków';
        $request->size = 10;

        $params = $request->toQueryParams();

        $this->assertEquals('Kraków', $params['searchText']);
        $this->assertEquals(10, $params['size']);
        $this->assertArrayNotHasKey('operators', $params);
        $this->assertArrayNotHasKey('fields', $params);
    }

    public function testToQueryParamsWithOperators(): void
    {
        $request = new PointSearchRequest();
        $request->searchText = 'Warszawa';
        $request->operators = [Operator::INPOST, Operator::DPD];

        $params = $request->toQueryParams();

        $this->assertEquals('INPOST,DPD', $params['operators']);
    }

    public function testToQueryParamsWithFields(): void
    {
        $request = new PointSearchRequest();
        $request->searchText = 'Wrocław';
        $request->fields = [PointField::CODE, PointField::CITY, PointField::STREET];

        $params = $request->toQueryParams();

        $this->assertEquals('code,city,street', $params['fields']);
    }

    public function testToQueryParamsWithAllParams(): void
    {
        $request = new PointSearchRequest();
        $request->searchText = 'Gdańsk';
        $request->size = 5;
        $request->operators = [Operator::INPOST];
        $request->posType = 'delivery';
        $request->cod = true;
        $request->fields = [PointField::CODE, PointField::OPERATOR];
        $request->pointTypes = ['parcel_locker'];

        $params = $request->toQueryParams();

        $this->assertEquals('Gdańsk', $params['searchText']);
        $this->assertEquals(5, $params['size']);
        $this->assertEquals('INPOST', $params['operators']);
        $this->assertEquals('delivery', $params['posType']);
        $this->assertEquals('true', $params['cod']);
        $this->assertEquals('code,operator', $params['fields']);
        $this->assertEquals('parcel_locker', $params['pointTypes']);
    }

    public function testToQueryParamsSkipsNullValues(): void
    {
        $request = new PointSearchRequest();
        $request->searchText = 'Poznań';

        $params = $request->toQueryParams();

        $this->assertCount(1, $params);
        $this->assertArrayHasKey('searchText', $params);
    }
}
