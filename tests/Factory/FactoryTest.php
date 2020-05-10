<?php
namespace Tests\Suilven\UKPostCodes\Factory;

use PHPUnit\Framework\TestCase;
use Suilven\UKPostCodes\API;
use Suilven\UKPostCodes\Factory\PostCodeFactory;
use Suilven\UKPostCodes\Models\Distance;
use Suilven\UKPostCodes\Models\PostCode;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Class FactoryTest
 * @package Tests\Suilven\UKPostCodes\Factory
 */

class FactoryTest extends TestCase
{
    /**
     * @test
     * @vcr factory/testCreation.yml
     * @group PhpVcrTest
     */
    public function testCreation()
    {
        $postcode = PostCodeFactory::get('KY16 9SS');
        $this->assertEquals('St Andrews', $postcode->admin_ward);
        $this->assertEquals('KY16 9SS', $postcode->postcode);
    }
}
