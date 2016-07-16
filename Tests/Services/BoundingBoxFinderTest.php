<?php
namespace VKR\GeolocationBundle\Tests\Services;

use VKR\GeolocationBundle\Exception\InvalidGeolocationValueException;
use VKR\GeolocationBundle\Services\BoundingBoxFinder;

class BoundingBoxFinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BoundingBoxFinder
     */
    protected $boundingBoxFinder;

    public function setUp()
    {
        $this->boundingBoxFinder = new BoundingBoxFinder();
    }

    public function testBoundingBoxAtEquator()
    {
        $lat = 0;
        $lng = 0;
        $allowance = 5;
        $boundingBox = $this->boundingBoxFinder->setBoundingBox($lat, $lng, $allowance);
        $this->assertEquals(-5, $boundingBox->latPair['min']);
        $this->assertEquals(5, $boundingBox->latPair['max']);
        $this->assertEquals(1, sizeof($boundingBox->lngPairs));
        $this->assertEquals(-5, $boundingBox->lngPairs[0]['min']);
        $this->assertEquals(5, $boundingBox->lngPairs[0]['max']);
    }

    public function testBoundingBoxAt60Deg()
    {
        // COS(60deg) == 0.5
        $lat = 60;
        $lng = 0;
        $allowance = 4;
        $boundingBox = $this->boundingBoxFinder->setBoundingBox($lat, $lng, $allowance);
        $this->assertEquals(56, $boundingBox->latPair['min']);
        $this->assertEquals(64, $boundingBox->latPair['max']);
        $this->assertEquals(1, sizeof($boundingBox->lngPairs));
        $this->assertEquals(-2, $boundingBox->lngPairs[0]['min']);
        $this->assertEquals(2, $boundingBox->lngPairs[0]['max']);
    }

    public function testBoundingBoxWithLatitudeWrap()
    {
        $lat = -80;
        $lng = 0;
        $allowance = 20;
        $boundingBox = $this->boundingBoxFinder->setBoundingBox($lat, $lng, $allowance);
        $this->assertEquals(-90, $boundingBox->latPair['min']);
        $this->assertEquals(-60, $boundingBox->latPair['max']);
    }

    public function testBoundingBoxWithLongitudeWrap()
    {
        $lat = 0;
        $lng = 170;
        $allowance = 30;
        $boundingBox = $this->boundingBoxFinder->setBoundingBox($lat, $lng, $allowance);
        $this->assertEquals(2, sizeof($boundingBox->lngPairs));
        $this->assertEquals(140, $boundingBox->lngPairs[0]['min']);
        $this->assertEquals(180, $boundingBox->lngPairs[0]['max']);
        $this->assertEquals(-180, $boundingBox->lngPairs[1]['min']);
        $this->assertEquals(-160, $boundingBox->lngPairs[1]['max']);
    }

    public function testBoundingBoxWithInvalidLatitude()
    {
        try {
            $lat = 100;
            $lng = 0;
            $allowance = 5;
            $boundingBox = $this->boundingBoxFinder->setBoundingBox($lat, $lng, $allowance);
        } catch (InvalidGeolocationValueException $e) {
            $this->assertContains('Latitude', $e->getMessage());
            return;
        }
        $this->fail("Expected Exception has not been raised");
    }

    public function testBoundingBoxWithInvalidLongitude()
    {
        try {
            $lat = 0;
            $lng = 200;
            $allowance = 5;
            $boundingBox = $this->boundingBoxFinder->setBoundingBox($lat, $lng, $allowance);
        } catch (InvalidGeolocationValueException $e) {
            $this->assertContains('Longitude', $e->getMessage());
            return;
        }
        $this->fail("Expected Exception has not been raised");
    }
}
