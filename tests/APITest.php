<?php


namespace Tests\Suilven\UKPostCodes;

use PHPUnit\Framework\TestCase;
use Suilven\UKPostCodes\API;
use Suilven\UKPostCodes\Models\PostCode;

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
     * @vcr testnearest.yml
     * @group PhpVcrTest
     */
    public function testNearest()
    {
        $response = $this->api->nearest('SW1A 2AA');
        error_log(print_r($response, 1));

    }

    /**
     * @test
     * @vcr testrandom.yml
     * @group PhpVcrTest
     */
    public function testRandom()
    {
        /** @var PostCode $random */
        $random = $this->api->random();
        $this->assertEquals('CM6 1EJ', $random->postcode);

        $lookup = $this->api->lookup('CM6 1EJ');
        $this->assertEquals($lookup, $random);


    }
}
