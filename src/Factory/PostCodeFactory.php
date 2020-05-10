<?php

namespace Suilven\UKPostCodes\Factory;

use Suilven\UKPostCodes\API;

/**
 * Class PostCodeFactory
 * @package Suilven\UKPostCodes\Factory
 */
class PostCodeFactory
{
    /**
     * @param string $postcodeString a UK postcode, e.g. KY16 2PY
     * @return \Suilven\UKPostCodes\Models\PostCode
     * @throws \Suilven\UKPostCodes\Exceptions\PostCodeServerException
     */
    public static function get($postcodeString)
    {
        $api = new API();
        return $api->lookup($postcodeString);
    }
}
