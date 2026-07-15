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

use Alsendo\AlsendoWrapper\Api\Geocoding\GeoUtils;
use Alsendo\AlsendoWrapper\Api\Geocoding\NominatimGeocodingService;
use Alsendo\AlsendoWrapper\Api\Points\Model\Point;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Pickup point search for RO (Ecolet API).
 *
 * Usage:
 *   $service = new EcoletPointService(['token' => 'xxx']);
 *   $points = $service->search('Bucuresti', 'sameday', 20);
 */
class EcoletPointService implements PickupPointServiceInterface
{
    private array $config;
    private bool $test;

    private ?Client $httpClient;
    private ?NominatimGeocodingService $geocoding;

    /** @var array<string, array{points: mixed, expires: int}> */
    private static array $cache = [];
    private const CACHE_TTL = 900;

    /**
     * @param array $config API credentials: ['token' => ...]
     * @param bool $test Use sandbox/test URLs
     * @param Client|null $httpClient Injectable HTTP client (for testing)
     * @param NominatimGeocodingService|null $geocoding Injectable geocoding (for testing)
     */
    public function __construct(
        array $config,
        bool $test = false,
        Client $httpClient = null,
        NominatimGeocodingService $geocoding = null
    ) {
        $this->config = $config;
        $this->test = $test;
        $this->httpClient = $httpClient;
        $this->geocoding = $geocoding;
    }

    /**
     * {@inheritDoc}
     */
    public function search(string $address, string $carrier = null, int $limit = 20): array
    {
        if (empty(trim($address))) {
            throw new \InvalidArgumentException('Address cannot be empty');
        }

        $client = $this->getHttpClient();

        // Step 1: Try Ecolet locality search (built-in geocoding)
        $localities = $this->fetchLocalities($client, $address);

        if (!empty($localities)) {
            $localityId = $localities[0]['id'] ?? null;
            if ($localityId !== null) {
                $points = $this->fetchPoints($client, ['localityId' => (int) $localityId], $carrier);

                if (!empty($points)) {
                    // Sort by distance from locality center if coords available
                    if (isset($localities[0]['lat'], $localities[0]['lng'])) {
                        $points = GeoUtils::sortByDistance(
                            $points,
                            (float) $localities[0]['lat'],
                            (float) $localities[0]['lng']
                        );
                    }

                    return array_slice($points, 0, $limit);
                }
            }
        }

        // Step 2: Fallback - Nominatim geocoding + all points + sort by distance
        $coords = $this->getGeocoding()->geocodeToCoordinates($address);
        $points = $this->fetchPoints($client, [], $carrier);
        $sorted = GeoUtils::sortByDistance($points, $coords['lat'], $coords['lon']);

        return array_slice($sorted, 0, $limit);
    }

    private function fetchLocalities(Client $client, string $query): array
    {
        if (strlen(trim($query)) < 3) {
            return [];
        }

        try {
            $response = $client->get('/api/v1/locations/ro/localities/' . urlencode($query));
            $decoded = json_decode((string) $response->getBody(), true);

            if (!is_array($decoded)) {
                return [];
            }

            return $decoded['data'] ?? $decoded;
        } catch (GuzzleException $e) {
            return [];
        }
    }

    /**
     * Fetch courier slug -> id mapping from Ecolet /api/v1/services endpoint.
     *
     * @return array<string, int> e.g. ['sameday' => 3, 'dpd' => 2, 'fan' => 1]
     */
    private function fetchCourierMap(Client $client): array
    {
        $cacheKey = 'ecolet_courier_map';
        $cached = self::$cache[$cacheKey] ?? null;
        if ($cached !== null && $cached['expires'] > time()) {
            return $cached['points']; // reusing 'points' key for the map
        }

        try {
            $response = $client->get('/api/v1/services');
            $decoded = json_decode((string) $response->getBody(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Ecolet services API request failed: ' . $e->getMessage(), 0, $e);
        }

        $services = $decoded['services'] ?? [];
        $map = [];
        foreach ($services as $service) {
            $courier = $service['courier'] ?? null;
            if ($courier !== null && isset($courier['id'], $courier['slug'])) {
                $slug = strtolower($courier['slug']);
                if (!isset($map[$slug])) {
                    $map[$slug] = (int) $courier['id'];
                }
            }
        }

        self::$cache[$cacheKey] = ['points' => $map, 'expires' => time() + self::CACHE_TTL];

        return $map;
    }

    /**
     * @return Point[]
     */
    private function fetchPoints(Client $client, array $bodyParams, ?string $carrier): array
    {
        $body = $bodyParams;
        $body['destination'] = 'receiver';
        if ($carrier !== null) {
            $courierMap = $this->fetchCourierMap($client);
            $carrierId = $courierMap[strtolower($carrier)] ?? null;
            if ($carrierId !== null) {
                $body['couriers'] = [$carrierId];
            }
            // if slug unknown - skip filter, return all points
        }

        try {
            $response = $client->post('/api/v1/map-points/ro', ['json' => $body]);
            $decoded = json_decode((string) $response->getBody(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Ecolet API request failed: ' . $e->getMessage(), 0, $e);
        }

        if (!is_array($decoded)) {
            return [];
        }

        $data = $decoded['mapPoints']['mapPoints'] ?? $decoded['data'] ?? $decoded;
        $points = [];

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            $locality = $item['locality'] ?? [];

            $point = new Point();
            $point->ecoletId = isset($item['id']) ? (int) $item['id'] : null;
            $point->code = (string) ($item['id'] ?? '');
            $point->name = $item['name'] ?? '';
            $point->street = $item['street'] ?? $item['address'] ?? '';
            $point->city = $locality['name'] ?? $item['city'] ?? '';
            $point->postalCode = $item['postal_code'] ?? '';
            $point->country = $item['country_code'] ?? 'RO';
            $point->latitude = isset($item['lat']) ? (float) $item['lat'] : null;
            $point->longitude = isset($item['lng']) ? (float) $item['lng'] : null;
            $point->carrier = $item['courier_slug'] ?? null;
            $point->operator = $item['courier_slug'] ?? null;
            $point->isLocker = isset($item['type']) && $item['type'] === 'locker';
            $point->address = implode(', ', array_filter([
                $item['street'] ?? $item['address'] ?? '',
                $locality['name'] ?? $item['city'] ?? '',
                $item['postal_code'] ?? '',
            ]));

            $points[] = $point;
        }

        return $points;
    }

    private function getHttpClient(): Client
    {
        if ($this->httpClient !== null) {
            return $this->httpClient;
        }

        $baseUrl = $this->test ? 'https://staging.ecolet.ro/' : 'https://panel.ecolet.ro/';
        $baseKey = method_exists(Client::class, 'request') ? 'base_uri' : 'base_url';

        return new Client([
            $baseKey => $baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . ($this->config['token'] ?? ''),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ]);
    }

    private function getGeocoding(): NominatimGeocodingService
    {
        if ($this->geocoding === null) {
            $this->geocoding = new NominatimGeocodingService();
        }

        return $this->geocoding;
    }

    /**
     * Clear static cache (for testing).
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
