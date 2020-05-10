<?php
namespace Suilven\UKPostCodes\Models;

/**
 * Class PostCode
 * @package Suilven\UKPostCodes\Models
 *
 * @property string|null $admin_county
 * @property string|null $admin_district
 * @property string|null $admin_ward
 * @property string|null $parish
 * @property string|null $parliamentary_constituency
 * @property string|null $ccg
 * @property string|null $ccg_id
 * @property string|null $ced
 * @property string|null $nuts
 */
class Codes
{
    /**
     * OutCode constructor.
     * @param array<array> $response
     */
    public function __construct($response)
    {
        $keys = array_keys($response);
        sort($keys);
        foreach ($keys as $key) {
            $this->$key = $response[$key];
        }
    }
}


