<?php
namespace VKR\GeolocationBundle\TestHelpers;

use VKR\GeolocationBundle\Interfaces\GeolocatableEntityInterface;

class GeolocatableEntity implements GeolocatableEntityInterface
{
    private $lat;
    private $lng;

    public function __construct($lat, $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }

    public function getLat()
    {
        return $this->lat;
    }

    public function getLng()
    {
        return $this->lng;
    }
}
