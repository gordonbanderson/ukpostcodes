<?php

namespace Suilven\UKPostCodes\Models;

use _HumbugBox7c6aed0dbb3c\Roave\BetterReflection\Reflection\Adapter\ReflectionObject;
use Suilven\UKPostCodes\API;

/**
 * Class PostCode
 * @package Suilven\UKPostCodes\Models
 *
 * @property string|null $admin_county
 * @property string|null $admin_district
 * @property string|null $admin_ward
 * @property string|null $ccg
 * @property string|null $ced
 *
 * @todo Review this one
 * @property string|null $codes
 *
 * @property string $country
 * @property int|null $eastings
 * @property string|null $european_electoral_region
 * @property string $incode
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int $lsoa
 * @property int $msoa
 * @property string|null $nhs_ha
 * @property int|null $northings
 * @property string|null $nuts
 * @property string $outcode
 * @property string|null $parish
 * @property string|null $parliamentary_constituency
 * @property string $postcode
 * @property string|null $primary_care_trust
 * @property int $quality
 * @property string|null $region
 * @property int|null $month_terminated
 * @property int|null $year_terminated
 * @property bool|null $terminated
 *
 * Distance is returned by some but not all API endpoints
 * @property float|null $distance

 */
class PostCode
{
    /**
     * PostCode constructor.
     * @param array<array> $response
     */
    public function __construct($response)
    {
        $keys = array_keys($response);
        sort($keys);
        foreach ($keys as $key) {
            if ($key != 'codes') {
                $this->$key = $response[$key];
            }
        }

        if (isset($response['codes'])) {
            $this->codes = new Codes($response['codes']);
        }
    }


    /**
     * Get the nearest postcodes to this postcode
     * @return array|PostCode[]
     * @throws \Suilven\UKPostCodes\Exceptions\PostCodeServerException
     */
    public function nearest()
    {
        $api = new API();
        return $api->nearest($this->postcode);
    }
}
