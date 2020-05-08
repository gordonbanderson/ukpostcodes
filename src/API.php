<?php

namespace Suilven\UKPostCodes;

//Based on code by Ryan Hart 2016 https://github.com/ryryan/Postcodes-IO-PHP/blob/master/Postcodes-IO-PHP.php

use Suilven\UKPostCodes\Exceptions\PostCodeServerException;
use Suilven\UKPostCodes\Models\PostCode;

class API
{
    /**
     * @param string $postcode
     * @return false|PostCode
     */
    public function lookup($postcode)
    {
        $jsonurl = "https://api.postcodes.io/postcodes/".$postcode;
        $json = $this->request($jsonurl);
        $decoded = json_decode($json, true);
        if ($decoded['status'] == 200) {
            return new PostCode($decoded['result']);
        } else {
            throw new PostCodeServerException('An error occurred whilst trying to lookup postcode');
        }
    }


    public function bulkLookup($postcodes)
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
        $decoded = json_decode($result);
        if ($decoded->status == 200) {
            return $decoded->result;
        } else {
            return false;
        }
        return false;
    }
    public function nearestPostcodesFromLongLat($longitude, $latitude)
    {
        $jsonurl = "https://api.postcodes.io/postcodes?lon=".$longitude."&lat=".$latitude;
        $json = $this->request($jsonurl);

        $decoded = json_decode($json);
        if ($decoded->status == 200) {
            return $decoded->result;
        } else {
            return false;
        }
        return false;
    }
    public function bulkReverseGeocoding($geolocations)
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
        $decoded = json_decode($result);
        if ($decoded->status == 200) {
            return $decoded->result;
        } else {
            return false;
        }
        return false;
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
        $jsonurl = "https://api.postcodes.io/postcodes/".$postcode."/validate";
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


    public function nearest($postcode)
    {
        $jsonurl = "https://api.postcodes.io/postcodes/".$postcode."/nearest";
        $json = $this->request($jsonurl);

        $decoded = json_decode($json);
        if ($decoded->status == 200) {
            return $decoded->result;
        } else {
            return false;
        }
        return false;
    }


    /**
     * Autocomplete a partial postcode
     *
     * @param string $partialPostCode The start of an incomplete postcode, e.g. KY12
     * @return array Array of postcode strings
     * @throws PostCodeServerException
     */
    public function partial($partialPostCode)
    {
        $jsonurl = "https://api.postcodes.io/postcodes/".$partialPostCode."/autocomplete";
        $json = $this->request($jsonurl);

        $decoded = json_decode($json, true);
        if ($decoded['status'] == 200) {
            return  $decoded['result'];
        } else {
            throw new PostCodeServerException("An error occurred whilst trying to autocomplete a postcode");
        }
    }

    /**
     * This is the same as partial() except that it returns in full the postcode details, not just the postcode string
     *
     * @param string $partialPostCode the partial postcode, e.g. KY16
     * @throws PostCodeServerException
     */
    public function query($partialPostCode) : array
    {
        $jsonurl = "https://api.postcodes.io/postcodes?q=".$partialPostCode;
        $json = $this->request($jsonurl);
        $decoded = json_decode($json, true);
        if ($decoded['status'] == 200) {
            $postcodesArray = [];
            foreach($decoded['result'] as $singlePostcodeDetails) {
                $postcodeObj = new PostCode($singlePostcodeDetails);
                $postcodesArray[] = $postcodeObj;
            }
            return $postcodesArray;
        } else {
            throw new PostCodeServerException('An error occurred whilst trying to lookup postcodes');
        }
    }


    public function lookupTerminated($postcode)
    {
        $jsonurl = "https://api.postcodes.io/terminated_postcodes/".$postcode;
        $json = $this->request($jsonurl);

        $decoded = json_decode($json);
        if ($decoded->status == 200) {
            return $decoded->result;
        } else {
            return false;
        }
        return false;
    }


    public function lookupOutwardCode($code)
    {
        $jsonurl = "https://api.postcodes.io/outcodes/".$code;
        $json = $this->request($jsonurl);

        $decoded = json_decode($json);
        if ($decoded->status == 200) {
            return $decoded->result;
        } else {
            return false;
        }
        return false;
    }


    public function nearestOutwardCode($code)
    {
        $jsonurl = "https://api.postcodes.io/outcodes/".$code."/nearest";
        $json = $this->request($jsonurl);

        $decoded = json_decode($json);
        if ($decoded->status == 200) {
            return $decoded->result;
        } else {
            return false;
        }
        return false;
    }


    public function nearestOutwardCodeFromLongLat($longitude, $latitude)
    {
        $jsonurl = "https://api.postcodes.io/outcodes?lon=".$longitude."&lat=".$latitude;
        $json = $this->request($jsonurl);

        $decoded = json_decode($json);
        if ($decoded->status == 200) {
            return $decoded->result;
        } else {
            return false;
        }
        return false;
    }


    /**
     * @return false|float
     */
    public function distance($postcode1, $postcode2, $unit)
    {
        //adapted from http://www.geodatasource.com/developers/php
        /*
            Units:
            M = Miles
            N = Nautical Miles
            K = Kilometers
        */
        $postcode1 = $this->lookup($postcode1);
        $postcode2 = $this->lookup($postcode2);

        if ($postcode1 == null || $postcode2 == null) {
            return false;
        }

        $theta = $postcode1->longitude - $postcode2->longitude;
        $dist = sin(deg2rad($postcode1->latitude)) * sin(deg2rad($postcode2->latitude)) +
            cos(deg2rad($postcode1->latitude)) * cos(deg2rad($postcode2->latitude)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } elseif ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    /**
     * Execute a request
     *
     * @param string $jsonurl
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
}
