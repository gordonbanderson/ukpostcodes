<?php

namespace Tests\Suilven\UKPostCodes\Models;

use PHPUnit\Framework\TestCase;
use Suilven\UKPostCodes\API;
use Suilven\UKPostCodes\Factory\PostCodeFactory;
use Suilven\UKPostCodes\Models\Distance;
use Suilven\UKPostCodes\Models\PostCode;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Class PostCodeTest
 * @package Tests\Suilven\UKPostCodes\Models
 */
class PostCodeTest extends TestCase
{
    /**
     * @test
     * @vcr models/testNearest.yml
     * @group PhpVcrTest
     */
    public function testNearest()
    {
        $postcode = PostCodeFactory::get('KY16 9SS');
        $this->assertEquals('St Andrews', $postcode->admin_ward);
        $this->assertEquals('KY16 9SS', $postcode->postcode);

        /** @var array<PostCode> $nearest */
        $nearest = $postcode->nearest();

        $postcodes = array_map(function ($p) {
            return $p->postcode;
        }, $nearest);
        $this->assertEquals([
            'KY16 9SS',
            'KY16 9ST',
            'KY16 9SX',
            'KY16 9TF',
            'KY16 9SR',
        ], $postcodes);

        // show the distances increasing
        $distances = array_map(function ($p) {
            return $p->distance;
        }, $nearest);
        $this->assertEquals([
            0,
            0,
            59.33866124,
            59.50103649,
            88.31936656,
        ], $distances);
    }
}
