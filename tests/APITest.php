<?php


namespace Tests\Suilven\UKPostCodes;

use PHPUnit\Framework\TestCase;
use Suilven\UKPostCodes\API;
use Suilven\UKPostCodes\Models\Distance;
use Suilven\UKPostCodes\Models\PostCode;
use Symfony\Component\VarExporter\VarExporter;

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
        /** @var array<PostCode> $nearest */
        $nearest = $this->api->nearest('SW1A 2AA');

        // check the returned values are all objeccts of class PostCode
        foreach ($nearest as $postcodeObj) {
            $this->assertEquals('Suilven\UKPostCodes\Models\PostCode', get_class($postcodeObj));
        }

        // assert the nearest postcodes
        $postcodes = array_map(function ($p) {
            return $p->postcode;
        }, $nearest);
        $this->assertEquals([
            'SW1A 2AA',
            'SW1A 2AB',
            'SW1A 2AD',
            'SW1A 2AG',
            'SW1A 2AL',
            'SW1A 2AS',
            'SW1A 2AT',
            'SW1A 2AU',
        ], $postcodes);
    }

    /**
     * @test
     * @vcr testnearestservererror.yml
     * @group PhpVcrTest
     */
    public function testNearestServerError()
    {
        $this->expectException('Suilven\UKPostCodes\Exceptions\PostCodeServerException');
        $this->expectExceptionMessage('An error occurred whilst trying to lookup nearest postcodes');

        /** @var array<PostCode> $nearest */
        $nearest = $this->api->nearest('SW1A 2AA');
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

        foreach ($postcodeObjs as $postcodeObj) {
            $this->assertEquals('Suilven\UKPostCodes\Models\PostCode', get_class($postcodeObj));
        }

        $postcodes = array_map(function ($p) {
            return $p->postcode;
        }, $postcodeObjs);

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
     * @vcr testqueryservererror.yml
     * @group PhpVcrTest
     */
    public function testQueryServerError()
    {
        $this->expectException('Suilven\UKPostCodes\Exceptions\PostCodeServerException');
        $this->expectExceptionMessage('An error occurred whilst trying to lookup postcodes');

        /** @var array $postcodeObjs */
        $postcodeObjs = $this->api->query('SW16');
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
     * @vcr testdistancemiles.yml
     * @group PhpVcrTest
     */
    public function testDistanceMiles()
    {
        /** @var float $distance Distance between Scottish and English parliaments */
        $distance = $this->api->distance('SW1A 2AB', 'EH99 1SP');

        $this->assertIsFloat($distance);
        $this->assertEquals(331.5486782092965, $distance);
    }

    /**
     * @test
     * @vcr testdistancenauticalmiles.yml
     * @group PhpVcrTest
     */
    public function testDistanceNauticalMiles()
    {
        /** @var float $distance Distance between Scottish and English parliaments */
        $distance = $this->api->distance('SW1A 2AB', 'EH99 1SP', Distance::NAUTICAL_MILES);

        $this->assertIsFloat($distance);
        $this->assertEquals(287.91687215695305, $distance);
    }

    /**
     * @test
     * @vcr testdistancenauticalkilometres.yml
     * @group PhpVcrTest
     */
    public function testDistanceKilometres()
    {
        /** @var float $distance Distance between Scottish and English parliaments */
        $distance = $this->api->distance('SW1A 2AB', 'EH99 1SP', Distance::KM);

        $this->assertIsFloat($distance);
        $this->assertEquals(533.5758759840621, $distance);
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
        $this->assertFalse($validated);
    }
}
