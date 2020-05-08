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
     * @vcr testlookupservererror.yml
     * @group PhpVcrTest
     */
    public function testLookupServerError()
    {
        $this->expectException('Suilven\UKPostCodes\Exceptions\PostCodeServerException');
        $this->expectExceptionMessage('An error occurred whilst trying to lookup postcode');

        $lookup = $this->api->lookup('SW1A 2AA');
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
     * @vcr testpartial.yml
     * @group PhpVcrTest
     */
    public function testPartial()
    {
        /** @var PostCode $partialPostcodeString */
        $partialPostcodeString = $this->api->partial('SW16');
        $this->assertEquals([
            'SW16 1AA',
            'SW16 1AB',
            'SW16 1AD',
            'SW16 1AE',
            'SW16 1AF',
            'SW16 1AG',
            'SW16 1AH',
            'SW16 1AJ',
            'SW16 1AL',
            'SW16 1AN',
        ], $partialPostcodeString);
    }

    /**
     * @test
     * @vcr testpartialservererror.yml
     * @group PhpVcrTest
     */
    public function testPartialServerError()
    {
        $this->expectException('Suilven\UKPostCodes\Exceptions\PostCodeServerException');
        $this->expectExceptionMessage('An error occurred whilst trying to autocomplete a postcode');

        /** @var PostCode $partialPostcodeString */
        $partialPostcodeString = $this->api->partial('SW16');
    }

    /**
     * @test
     * @vcr testquery.yml
     * @group PhpVcrTest
     */
    public function testQuery()
    {
        /** @var array $postcodeObjs */
        $postcodeObjs = $this->api->query('SW16');

        foreach($postcodeObjs as $postcodeObj) {
            $this->assertEquals('Suilven\UKPostCodes\Models\PostCode', get_class($postcodeObj));
        }

        $postcodes = array_map(function ($p) { return $p->postcode; }, $postcodeObjs);

        $this->assertEquals([
            'SW16 1AA',
            'SW16 1AB',
            'SW16 1AD',
            'SW16 1AE',
            'SW16 1AF',
            'SW16 1AG',
            'SW16 1AH',
            'SW16 1AJ',
            'SW16 1AL',
            'SW16 1AN',
        ], $postcodes);
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

        $lookup = $this->api->lookup($random->postcode);
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
        $this->expectExceptionMessage('An error occurred whilst trying to select a random postcode');

        /** @var PostCode $random */
        $random = $this->api->random();
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
