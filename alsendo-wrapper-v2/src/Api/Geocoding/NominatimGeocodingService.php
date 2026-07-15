<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Geocoding;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Geocoding\Model\GeocodingResult;
use Alsendo\AlsendoWrapper\ApiClient;

class NominatimGeocodingService extends ApiClient
{
    private const BASE_URL = 'https://nominatim.openstreetmap.org';
    private const USER_AGENT = 'Alsendo-Wrapper/2.0';

    public function __construct()
    {
        parent::__construct(self::BASE_URL, [], [
            'headers' => [
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * @throws GeocodingException
     */
    public function geocode(string $address): GeocodingResult
    {
        if (trim($address) === '') {
            throw new GeocodingException('Address cannot be empty');
        }

        $response = $this->makeRequest('GET', '/search', [
            'query' => [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
            ],
        ]);

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new GeocodingException('Invalid JSON response from Nominatim: ' . json_last_error_msg());
        }

        if (empty($data)) {
            throw new GeocodingException('No results found for address: ' . $address);
        }

        $first = $data[0];

        return new GeocodingResult(
            (float) $first['lat'],
            (float) $first['lon'],
            $first['display_name'] ?? ''
        );
    }

    /**
     * @return array{lat: float, lon: float}
     *
     * @throws GeocodingException
     */
    public function geocodeToCoordinates(string $address): array
    {
        $result = $this->geocode($address);

        return [
            'lat' => $result->lat,
            'lon' => $result->lon,
        ];
    }
}
