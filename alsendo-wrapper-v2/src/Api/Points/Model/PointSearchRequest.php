<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Points\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Model\Request;

class PointSearchRequest extends Request
{
    public ?string $searchText = null;
    public ?int $size = null;
    /** @var string[] Operator constants */
    public array $operators = [];
    public ?string $posType = null;
    public ?bool $cod = null;
    /** @var string[] PointField constants */
    public array $fields = [];
    /** @var string[] */
    public ?array $pointTypes = null;
    public ?float $lat = null;
    public ?float $lon = null;
    public ?float $distance = null;
    public ?string $distanceUnit = null;

    public function toQueryParams(): array
    {
        $params = [];

        if ($this->searchText !== null && $this->searchText !== '') {
            $params['searchText'] = $this->searchText;
        }

        if ($this->size !== null) {
            $params['size'] = $this->size;
        }

        if (!empty($this->operators)) {
            $params['operators'] = implode(',', $this->operators);
        }

        if ($this->posType !== null) {
            $params['posType'] = $this->posType;
        }

        if ($this->cod !== null) {
            $params['cod'] = $this->cod ? 'true' : 'false';
        }

        if (!empty($this->fields)) {
            $params['fields'] = implode(',', $this->fields);
        }

        if ($this->pointTypes !== null && !empty($this->pointTypes)) {
            $params['pointTypes'] = implode(',', $this->pointTypes);
        }

        if ($this->lat !== null) {
            $params['lat'] = $this->lat;
        }

        if ($this->lon !== null) {
            $params['lon'] = $this->lon;
        }

        if ($this->distance !== null) {
            $params['distance'] = $this->distance;
        }

        if ($this->distanceUnit !== null) {
            $params['distanceUnit'] = $this->distanceUnit;
        }

        return $params;
    }

    public function toArray(array $exclude = [], bool $convertToSnakeCase = true): array
    {
        return $this->toQueryParams();
    }
}
