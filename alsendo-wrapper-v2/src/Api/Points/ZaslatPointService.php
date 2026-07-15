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
 * Pickup point search for CZ (Zaslat API).
 *
 * Usage:
 *   $service = new ZaslatPointService(['api_key' => 'xxx']);
 *   $points = $service->search('Praha', 'PPL', 20);
 */
class ZaslatPointService implements PickupPointServiceInterface
{
    private array $config;
    private bool $test;

    private ?Client $httpClient;
    private ?NominatimGeocodingService $geocoding;

    /** @var array<string, array{points: Point[], expires: int}> */
    private static array $cache = [];
    private const CACHE_TTL = 900;

    /**
     * @param array $config API credentials: ['api_key' => ...]
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

        $coords = $this->getGeocoding()->geocodeToCoordinates($address);

        $allPoints = $this->fetchPoints($carrier);

        $sorted = GeoUtils::sortByDistance($allPoints, $coords['lat'], $coords['lon']);

        return array_slice($sorted, 0, $limit);
    }

    /**
     * @return Point[]
     */
    private function fetchPoints(?string $carrier): array
    {
        $cacheKey = 'zaslat_CZ_' . ($carrier ?? 'all');
        $cached = self::$cache[$cacheKey] ?? null;
        if ($cached !== null && $cached['expires'] > time()) {
            return $cached['points'];
        }

        $client = $this->getHttpClient();

        $query = ['country' => 'CZ'];
        if ($carrier !== null) {
            $query['carrier'] = $carrier;
        }

        try {
            $response = $client->get('/api/v1/parcelpoints/list', ['query' => $query]);
            $decoded = json_decode((string) $response->getBody(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Zaslat API request failed: ' . $e->getMessage(), 0, $e);
        }

        if (!is_array($decoded)) {
            return [];
        }

        $data = $decoded['data'] ?? $decoded;
        $points = [];

        foreach ($data as $id => $item) {
            if (!is_array($item)) {
                continue;
            }

            $loc = $item['location'] ?? [];

            $point = new Point();
            $point->code = (string) ($item['code'] ?? $id);
            $point->name = $item['name'] ?? '';
            $point->street = $item['street'] ?? '';
            $point->city = $item['city'] ?? '';
            $point->postalCode = $item['zip'] ?? '';
            $point->country = $item['country'] ?? 'CZ';
            $point->latitude = isset($loc['lat']) ? (float) $loc['lat'] : null;
            $point->longitude = isset($loc['lng']) ? (float) $loc['lng'] : null;
            $point->carrier = $item['carrier'] ?? null;
            $point->operator = $item['carrier'] ?? null;
            $point->isLocker = (bool) ($item['is_locker'] ?? false);
            $point->address = implode(', ', array_filter([
                $item['street'] ?? '', $item['city'] ?? '', $item['zip'] ?? '',
            ]));

            $points[] = $point;
        }

        self::$cache[$cacheKey] = ['points' => $points, 'expires' => time() + self::CACHE_TTL];

        return $points;
    }

    private function getHttpClient(): Client
    {
        if ($this->httpClient !== null) {
            return $this->httpClient;
        }

        $baseUrl = $this->test ? 'https://test.zaslat.cz/' : 'https://www.zaslat.cz/';
        $baseKey = method_exists(Client::class, 'request') ? 'base_uri' : 'base_url';

        return new Client([
            $baseKey => $baseUrl,
            'headers' => [
                'X-Apikey' => $this->config['api_key'] ?? '',
                'Accept' => 'application/json',
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
