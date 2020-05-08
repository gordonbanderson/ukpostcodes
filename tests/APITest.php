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

    /**
     * @test
     * @vcr testrandomservererror.yml
     * @group PhpVcrTest
     */
    public function testRandomServerError()
    {
        $this->expectException('Suilven\UKPostCodes\Exceptions\PostCodeServerException');
        $this->expectExceptionMessage('An error occurred whilst trying to validate');

        /** @var PostCode $random */
        $random = $this->api->random();
        $this->assertEquals('AB16 7LR', $random->postcode);
    }

    /**
     * @test
     * @vcr testvalidatevalid.yml
     * @group PhpVcrTest
     */
    public function testValidateValid()
    {
        /** @var PostCode $random */
        $validated = $this->api->validate('KY16 9SS');
        error_log(print_r($validated, 1));
        $this->assertTrue($validated);
    }

    /**
     * @test
     * @vcr testvalidateinvalid.yml
     * @group PhpVcrTest
     */
    public function testValidateInvalid()
    {
        /** @var PostCode $validated */
        $validated = $this->api->validate('KYAB92A');
        error_log(print_r($validated, 1));
        $this->assertFalse($validated);
    }

    /**
     * @test
     * @vcr testvalidateinvalidservererror.yml
     * @group PhpVcrTest
     */
    public function testValidateInvalidServerError()
    {
        $this->expectException('Suilven\UKPostCodes\Exceptions\PostCodeServerException');
        $this->expectExceptionMessage('An error occurred whilst trying to validate');
        /** @var PostCode $validated */
        $validated = $this->api->validate('KYAB92A');
        error_log(print_r($validated, 1));
        $this->assertFalse($validated);
    }
}
