<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Points;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Geocoding\GeocodingException;
use Alsendo\AlsendoWrapper\Api\Geocoding\NominatimGeocodingService;
use Alsendo\AlsendoWrapper\Api\Points\Model\Point;
use Alsendo\AlsendoWrapper\Api\Points\Model\PointSearchRequest;
use Alsendo\AlsendoWrapper\ApiClient;
use Alsendo\AlsendoWrapper\Exception\ResponseException;
use Alsendo\AlsendoWrapper\Exception\ValidationException;
use Alsendo\AlsendoWrapper\Json;

class BliskaPaczkaPointService extends ApiClient implements PointServiceInterface
{
    private const PRODUCTION_URL = 'https://pos.bliskapaczka.pl/';
    private const SANDBOX_URL = 'https://pos.sandbox-bliskapaczka.pl/';

    public function __construct(bool $test = false)
    {
        $apiUrl = $test ? self::SANDBOX_URL : self::PRODUCTION_URL;
        parent::__construct($apiUrl, [], []);
    }

    /**
     * @return Point[]
     *
     * @throws ValidationException
     * @throws ResponseException
     */
    public function search(PointSearchRequest $request): array
    {
        $hasSearchText = $request->searchText !== null && $request->searchText !== '';
        $hasCoordinates = $request->lat !== null && $request->lon !== null;

        if (!$hasSearchText && !$hasCoordinates) {
            throw new ValidationException(['search' => ['Wymagany jest searchText lub wspolrzedne (lat + lon)']]);
        }

        $response = $this->makeRequest('GET', '/api/v1/pos/filter', [
            'query' => $request->toQueryParams(),
        ]);

        $data = $this->parseResponse($response);

        return array_map(
            function (array $item) {
                return Json::mapArrayToObject($item, Point::class);
            },
            $data
        );
    }

    /**
     * @param string $operator Operator constant value
     * @param string $code Point code
     *
     * @throws ResponseException
     */
    public function getPoint(string $operator, string $code): Point
    {
        $response = $this->makeRequest(
            'GET',
            '/api/v1/pos/' . $operator . '/' . $code
        );

        $data = $this->parseResponse($response);

        return Json::mapArrayToObject($data, Point::class);
    }

    /**
     * @return Point[]
     *
     * @throws GeocodingException
     * @throws ValidationException
     * @throws ResponseException
     */
    public function searchByAddress(
        string $address,
        float $distance = null,
        ?string $distanceUnit = 'KM',
        int $size = null,
        array $operators = [],
        string $posType = null
    ): array {
        $geocoding = new NominatimGeocodingService();
        $coords = $geocoding->geocodeToCoordinates($address);

        $request = new PointSearchRequest();
        $request->lat = $coords['lat'];
        $request->lon = $coords['lon'];

        if ($distance !== null) {
            $request->distance = $distance;
        }
        if ($distanceUnit !== null) {
            $request->distanceUnit = $distanceUnit;
        }
        if ($size !== null) {
            $request->size = $size;
        }
        if (!empty($operators)) {
            $request->operators = $operators;
        }
        if ($posType !== null) {
            $request->posType = $posType;
        }

        return $this->search($request);
    }

    /**
     * @throws ResponseException
     */
    private function parseResponse(string $response): array
    {
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ResponseException('Invalid JSON response: ' . json_last_error_msg(), 500);
        }

        return $data;
    }
}
