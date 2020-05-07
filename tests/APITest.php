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
     * @vcr getpostcode.yml
     * @group PhpVcrTest
     */
    public function testGetPostCode()
    {
        $response = $this->api->query('SW1A 2AA');
        error_log(print_r($response, 1));
    }
}
