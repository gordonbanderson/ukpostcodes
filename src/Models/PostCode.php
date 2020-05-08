<?php

namespace Suilven\UKPostCodes\Models;

use _HumbugBox7c6aed0dbb3c\Roave\BetterReflection\Reflection\Adapter\ReflectionObject;

/**
 * Class PostCode
 * @package Suilven\UKPostCodes\Models
 *
 * * @property int $admin_county
* @property int $admin_district
* @property int $admin_ward
* @property int $ccg
* @property int $ced
* @property int $codes
* @property int $country
* @property int $eastings
* @property int $european_electoral_region
* @property int $incode
* @property int $latitude
* @property int $longitude
* @property int $lsoa
* @property int $msoa
* @property int $nhs_ha
* @property int $northings
* @property int $nuts
* @property int $outcode
* @property int $parish
* @property int $parliamentary_constituency
* @property int $postcode
* @property int $primary_care_trust
* @property int $quality
* @property int $region

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
            //error_log('* @property int $' . $key);
            $this->$key = $response[$key];
        }
    }
}
