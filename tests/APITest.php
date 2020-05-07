<?php


namespace Tests\Suilven\UKPostCodes;

use PHPUnit\Framework\TestCase;
use Suilven\UKPostCodes\API;

class APITest extends TestCase
{
    /**
     * @var API
     */
    private $api;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = new API();
    }

    /**
     * @test
     * @vcr testlookup.yml
     * @group PhpVcrTest
     */
    public function testLookup()
    {
        $lookup = $this->api->lookup('SW1A 2AA');
        error_log(print_r($lookup, 1));

        $this->assertEquals('SW1A 2AA', $lookup->postcode);
    }

    /**
     * @test
     * @vcr getnearest.yml
     * @group PhpVcrTest
     */
    public function testGetNearest()
    {
        $response = $this->api->nearest('SW1A 2AA');
        error_log(print_r($response, 1));

    }
}
