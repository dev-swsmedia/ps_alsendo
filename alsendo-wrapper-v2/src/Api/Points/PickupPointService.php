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

use Alsendo\AlsendoWrapper\Api\Geocoding\NominatimGeocodingService;
use GuzzleHttp\Client;

/**
 * Unified pickup point search - facade/delegator for all supported countries.
 *
 * Delegates to the appropriate region-specific service:
 *   PL -> BliskaPaczkaPointService (searchByAddress + enrichment)
 *   CZ -> ZaslatPointService
 *   RO -> EcoletPointService
 *
 * Usage:
 *   $service = new PickupPointService('cz', ['api_key' => 'xxx']);
 *   $points = $service->search('Praha', 'PPL', 20);
 *
 *   $service = new PickupPointService('ro', ['token' => 'xxx']);
 *   $points = $service->search('Bucuresti');
 *
 *   $service = new PickupPointService('pl');
 *   $points = $service->search('Kraków', 'INPOST', 10);
 */
class PickupPointService implements PickupPointServiceInterface
{
    private string $country;
    private array $config;
    private bool $test;

    // Injectable deps for testing
    private ?Client $httpClient;
    private ?NominatimGeocodingService $geocoding;
    private ?BliskaPaczkaPointService $bliskaPaczka;

    // Lazy-created region services
    private ?ZaslatPointService $zaslatService = null;
    private ?EcoletPointService $ecoletService = null;

    /**
     * @param string $country Country code: 'pl', 'cz', 'ro'
     * @param array $config API credentials: ['api_key' => ...] for CZ, ['token' => ...] for RO, [] for PL
     * @param bool $test Use sandbox/test URLs
     * @param Client|null $httpClient Injectable HTTP client (for testing)
     * @param NominatimGeocodingService|null $geocoding Injectable geocoding (for testing)
     * @param BliskaPaczkaPointService|null $bliskaPaczka Injectable PL service (for testing)
     */
    public function __construct(
        string $country,
        array $config = [],
        bool $test = false,
        Client $httpClient = null,
        NominatimGeocodingService $geocoding = null,
        BliskaPaczkaPointService $bliskaPaczka = null
    ) {
        $this->country = strtolower($country);
        $this->config = $config;
        $this->test = $test;
        $this->httpClient = $httpClient;
        $this->geocoding = $geocoding;
        $this->bliskaPaczka = $bliskaPaczka;
    }

    /**
     * {@inheritDoc}
     */
    public function search(string $address, string $carrier = null, int $limit = 20): array
    {
        if (empty(trim($address))) {
            throw new \InvalidArgumentException('Address cannot be empty');
        }

        switch ($this->country) {
            case 'pl': return $this->searchPL($address, $carrier, $limit);
            case 'cz': return $this->getZaslatService()->search($address, $carrier, $limit);
            case 'ro': return $this->getEcoletService()->search($address, $carrier, $limit);
            default: throw new \InvalidArgumentException("Unsupported country: {$this->country}");
        }
    }

    // ── PL (BliskaPaczka) ──────────────────────────────────────────────

    private function searchPL(string $address, ?string $carrier, int $limit): array
    {
        $service = $this->bliskaPaczka ?? new BliskaPaczkaPointService($this->test);

        $operators = [];
        if ($carrier !== null) {
            $operators = [$carrier];
        }

        $points = $service->searchByAddress($address, 50.0, 'KM', $limit, $operators);

        foreach ($points as $point) {
            $point->country = 'PL';
            $point->carrier = $point->operator;
            $point->isLocker = is_array($point->pointTypes) && in_array('parcel_locker', $point->pointTypes);
        }

        return $points;
    }

    // ── Lazy service getters ───────────────────────────────────────────

    private function getZaslatService(): ZaslatPointService
    {
        if ($this->zaslatService === null) {
            $this->zaslatService = new ZaslatPointService(
                $this->config,
                $this->test,
                $this->httpClient,
                $this->geocoding
            );
        }

        return $this->zaslatService;
    }

    private function getEcoletService(): EcoletPointService
    {
        if ($this->ecoletService === null) {
            $this->ecoletService = new EcoletPointService(
                $this->config,
                $this->test,
                $this->httpClient,
                $this->geocoding
            );
        }

        return $this->ecoletService;
    }

    /**
     * Clear static cache on all region services (for testing).
     */
    public static function clearCache(): void
    {
        ZaslatPointService::clearCache();
        EcoletPointService::clearCache();
    }
}
