<?php
namespace VKR\GeolocationBundle\Tests\Services;

use PHPUnit\Framework\TestCase;
use VKR\GeolocationBundle\Exception\InvalidGeolocationValueException;
use VKR\GeolocationBundle\Services\NearestPointCalculator;
use VKR\GeolocationBundle\TestHelpers\GeolocatableEntity;

class NearestPointCalculatorTest extends TestCase
{
    /**
     * @var NearestPointCalculator
     */
    private $calculator;

    public function setUp()
    {
        $this->calculator = new NearestPointCalculator();
    }

    public function testNearestPoint()
    {
        $data = [
            new GeolocatableEntity(0, 0),
            new GeolocatableEntity(20, -20),
            new GeolocatableEntity(18, -10),
        ];
        $index = $this->calculator->findNearestPoint(5, 5, $data);
        $this->assertEquals(0, $index);
        $index = $this->calculator->findNearestPoint(19, -20, $data);
        $this->assertEquals(1, $index);
        $index = $this->calculator->findNearestPoint(19, -14, $data);
        $this->assertEquals(2, $index);
    }

    public function testPointsAcrossIDL()
    {
        $data = [
            new GeolocatableEntity(0, 160),
            new GeolocatableEntity(0, -178),
        ];
        $index = $this->calculator->findNearestPoint(0, 178, $data);
        $this->assertEquals(1, $index);
    }

    public function testInvalidPoints()
    {
        $data = [
            new GeolocatableEntity(100, 160),
            new GeolocatableEntity(0, -220),
        ];
        $this->expectException(InvalidGeolocationValueException::class);
        $this->calculator->findNearestPoint(0, 0, $data);
    }
}
