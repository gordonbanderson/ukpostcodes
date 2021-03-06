<?php

namespace Suilven\UKPostCodes;

//Based on code by Ryan Hart 2016 https://github.com/ryryan/Postcodes-IO-PHP/blob/master/Postcodes-IO-PHP.php

use Hoa\Stream\IStream\Out;
use Suilven\UKPostCodes\Exceptions\PostCodeServerException;
use Suilven\UKPostCodes\Models\Distance;
use Suilven\UKPostCodes\Models\OutCode;
use Suilven\UKPostCodes\Models\PostCode;

class API
{
    /**
     * @param string $postcode
     *
     * @return PostCode
     */
    public function lookup($postcode): PostCode
    {
        $jsonurl = "https://api.postcodes.io/postcodes/" . $postcode;
        $json = $this->request($jsonurl);
        $decoded = json_decode($json, true);
        if ($decoded['status'] == 200) {
            return new PostCode($decoded['result']);
        } else {
            throw new PostCodeServerException('An error occurred whilst trying to lookup postcode');
        }
    }


    /**
     * @param string $postcodes postcodes as strings, e.g SW9 YSS
     *
     * @return PostCode[]
     *
     * @throws PostCodeServerException
     *
     * @psalm-return list<PostCode>
     */
    public function bulkLookup($postcodes): array
    {
        $data_string = json_encode(array('postcodes' => $postcodes));
        $ch = curl_init('https://api.postcodes.io/postcodes');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)));

        $result = curl_exec($ch);
        curl_close($ch);
        $decoded = json_decode($result, true);

        $postcodes = [];

        if ($decoded['status'] == 200) {
            foreach ($decoded['result'] as $singleResponse) {
                $result = $singleResponse['result'];
                $postcode = new PostCode($result);
                $postcodes[] = $postcode;
            }
            return $postcodes;
        } else {
            throw new PostCodeServerException('Bulk lookup of postcodes failed');
        }
    }


    /**
     * @param float $longitude longitude of given coordinate
     * @param float $latitude latitude of given coordinate
     * @return array<PostCode>
     * @throws PostCodeServerException
     */
    public function nearestPostcodesFromLongLat($longitude, $latitude): array
    {
        $jsonurl = "https://api.postcodes.io/postcodes?lon=" . $longitude . "&lat=" . $latitude;
        $json = $this->request($jsonurl);

        $decoded = json_decode($json, true);
        if ($decoded['status'] == 200) {
            return $this->parsePostCodeArray($decoded['result']);
        } else {
            throw new PostCodeServerException(
                'An error occurred whilst trying to lookup postcode for given coordinates'
            );
        }
    }

    /**
     * @param array $geolocations an array of arrays keyed with latitude and longitude
     *
     * @return PostCode[][] An array of arrays of PostCode
     *
     * @throws PostCodeServerException if a server error occurs
     *
     * @psalm-return list<list<PostCode>>
     */
    public function bulkReverseGeocoding($geolocations): array
    {
        $data_string = json_encode(array('geolocations' => $geolocations));

        $ch = curl_init('https://api.postcodes.io/postcodes');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)));

        $result = curl_exec($ch);
        curl_close($ch);
        $decoded = json_decode($result, true);

        $postcodes = [];

        if ($decoded['status'] == 200) {
            // this is an array of arrays, the sub array is keyed query and result
            $response = $decoded['result'];

            foreach ($response as $queryResult) {
                $postcodesForQuery = [];

                $postcodeArrays = $queryResult['result'];
                foreach ($postcodeArrays as $postcodeArray) {
                    $postcode = new PostCode($postcodeArray);
                    $postcodesForQuery[] = $postcode;
                }
                $postcodes[] = $postcodesForQuery;
            }
            return $postcodes;
        } else {
            throw new PostCodeServerException('Bulk reverse geocoding of postcodes failed');
        }
    }


    /**
     * Returns an entirely random postcode
     *
     * @return PostCode a random PostCode
     *
     * @throws PostCodeServerException
     */
    public function random()
    {
        $jsonurl = "https://api.postcodes.io/random/postcodes/";
        $json = $this->request($jsonurl);

        $decoded = json_decode($json, true);
        if ($decoded['status'] == 200) {
            return new PostCode($decoded['result']);
        } else {
            throw new PostCodeServerException("An error occurred whilst trying to select a random postcode");
        }
    }

    /**
     * Ascertain whether a provided postcode string is valid or not
     *
     * @param string $postcode
     * @return bool true if the postcode is valid, false if not
     * @throws PostCodeServerException
     */
    public function validate($postcode): bool
    {
        $jsonurl = "https://api.postcodes.io/postcodes/" . $postcode . "/validate";
        $json = $this->request($jsonurl);

        $decoded = json_decode($json);
        if ($decoded->status == 200) {
            if ($decoded->result == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new PostCodeServerException('An error occurred whilst trying to validate');
        }
    }


    /**
     * @param string $postcode
     * @return array<PostCode> PostCode objects near the original postcode specifed by a string
     * @throws PostCodeServerException
     */
    public function nearest($postcode)
    {
        $jsonurl = "https://api.postcodes.io/postcodes/" . $postcode . "/nearest";
        $json = $this->request($jsonurl);

        $decoded = json_decode($json, true);
        if ($decoded['status'] == 200) {
            return $this->parsePostCodeArray($decoded['result']);
        } else {
            throw new PostCodeServerException('An error occurred whilst trying to lookup nearest postcodes');
        }
    }


    /**
     * Autocomplete a partial postcode
     *
     * @param string $partialPostCode The start of an incomplete postcode, e.g. KY12
     * @return array<string> Array of postcode strings
     * @throws PostCodeServerException
     */
    public function partial($partialPostCode)
    {
        $jsonurl = "https://api.postcodes.io/postcodes/" . $partialPostCode . "/autocomplete";
        $json = $this->request($jsonurl);

        $decoded = json_decode($json, true);
        if ($decoded['status'] == 200) {
            return $decoded['result'];
        } else {
            throw new PostCodeServerException("An error occurred whilst trying to autocomplete a postcode");
        }
    }

    /**
     * This is the same as partial() except that it returns in full the postcode details, not just the postcode string
     *
     * @param string $partialPostCode the partial postcode, e.g. KY16
     * @return array<PostCode> an array of PostCode objects
     * @throws PostCodeServerException
     */
    public function query($partialPostCode): array
    {
        $jsonurl = "https://api.postcodes.io/postcodes?q=" . $partialPostCode;
        $json = $this->request($jsonurl);
        $decoded = json_decode($json, true);
        if ($decoded['status'] == 200) {
            return $this->parsePostCodeArray($decoded['result']);
        } else {
            throw new PostCodeServerException('An error occurred whilst trying to lookup postcodes');
        }
    }


    /**
     * Lookup a terminated postcode
     *
     * @param string $postcode a terminated postcode
     * @return PostCode a partially populated postcode object, as most info is missing
     * @throws PostCodeServerException If the postcode in question has been terminated
     */
    public function lookupTerminated($postcode)
    {
        $jsonurl = "https://api.postcodes.io/terminated_postcodes/" . $postcode;
        $json = $this->request($jsonurl);

        $decoded = json_decode($json, true);
        if ($decoded['status'] == 200) {
            $postcode = new PostCode($decoded['result']);
            $postcode->terminated = true;
            return $postcode;
        } else {
            throw new PostCodeServerException("An error occurred whilst trying to select a terminated postcode");
        }
    }


    /**
     * @param string $code An outcode, e.g. DD11
     * @return OutCode data relevant to the provided outcode
     * @throws PostCodeServerException if a server error occurs
     */
    public function lookupOutwardCode($code)
    {
        $jsonurl = "https://api.postcodes.io/outcodes/" . $code;
        $json = $this->request($jsonurl);

        $decoded = json_decode($json, true);

        if ($decoded['status'] == 200) {
            return new OutCode($decoded['result']);
        } else {
            throw new PostCodeServerException("An error occurred whilst trying to execute lookupOutwardCode");
        }
    }


    /**
     * @param string $code An outcode, e.g. DD11
     * @return array<OutCode> An array of nearest outcodes
     * @throws PostCodeServerException
     */
    public function nearestOutwardCode($code)
    {
        $jsonurl = "https://api.postcodes.io/outcodes/" . $code . "/nearest";
        $json = $this->request($jsonurl);

        $decoded = json_decode($json, true);

        if ($decoded['status'] == 200) {
            return $this->parseOutCodeArray($decoded['result']);
        } else {
            throw new PostCodeServerException("An error occurred whilst trying to execute nearestOutwardCode");
        }
    }

    /**
     * @param float $longitude the longitude of the coordinate
     * @param float $latitude the latitude of the coordinate
     * @return array<OutCode>
     */
    public function nearestOutwardCodeFromLongLat($longitude, $latitude): array
    {
        $jsonurl = "https://api.postcodes.io/outcodes?lon=" . $longitude . "&lat=" . $latitude;
        $json = $this->request($jsonurl);

        $decoded = json_decode($json, true);

        if ($decoded['status'] == 200) {
            return $this->parseOutCodeArray($decoded['result']);
        } else {
            throw new PostCodeServerException("An error occurred whilst trying to autocomplete a postcode");
        }
    }


    /**
     * @param string $postcode1 the source postcode
     * @param string $postcode2 the destination postcode
     * @param string $unit either M, N, or K for miles, nautical miles, or km
     * @return float the distance in the units of distance
     * @throws PostCodeServerException if a server error occurs or the postcodes are cannot be found
     */
    public function distance($postcode1, $postcode2, $unit = Distance::MILES)
    {
        //adapted from http://www.geodatasource.com/developers/php
        /*
            Units:
            M = Miles
            N = Nautical Miles
            K = Kilometers
        */

        try {
            $postcode1 = $this->lookup($postcode1);
            $postcode2 = $this->lookup($postcode2);
        } catch (PostCodeServerException $ex) {
            throw new PostCodeServerException("One or both of {$postcode1} & {$postcode2} are invalid");
        }

        $theta = $postcode1->longitude - $postcode2->longitude;
        $dist = sin(deg2rad($postcode1->latitude)) * sin(deg2rad($postcode2->latitude)) +
            cos(deg2rad($postcode1->latitude)) * cos(deg2rad($postcode2->latitude)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == Distance::KM) {
            return ($miles * 1.609344);
        } elseif ($unit == Distance::NAUTICAL_MILES) {
            return ($miles * 0.8684);
        } else {
            // miles case
            return $miles;
        }
    }

    /**
     * Execute a request
     *
     * @param string $jsonurl
     *
     * @return bool|string
     */
    public function request($jsonurl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, str_replace(' ', '%20', $jsonurl));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }


    /**
     * Convert the response of several postcodes into an array of PostCode objects
     *
     * @param array $postcodeArrayResponse
     *
     * @return PostCode[]
     *
     * @psalm-return list<PostCode>
     */
    private function parsePostCodeArray($postcodeArrayResponse): array
    {
        $postcodesArray = [];

        if (!empty($postcodeArrayResponse)) {
            foreach ($postcodeArrayResponse as $singlePostcodeDetails) {
                $postcodeObj = new PostCode($singlePostcodeDetails);
                $postcodesArray[] = $postcodeObj;
            }
        }

        return $postcodesArray;
    }


    /**
     * Convert the response of several outcodes into an array of OutCode objects
     *
     * @param array $outcodesArrayResponse Response from the postcodes.io server
     *
     * @return OutCode[]
     *
     * @psalm-return list<OutCode>
     */
    private function parseOutCodeArray($outcodesArrayResponse): array
    {
        $outcodesArray = [];

        if (!empty($outcodesArrayResponse)) {
            foreach ($outcodesArrayResponse as $singleOutCodeDetails) {
                $outcodeObj = new OutCode($singleOutCodeDetails);
                $outcodesArray[] = $outcodeObj;
            }
        }

        return $outcodesArray;
    }
}
