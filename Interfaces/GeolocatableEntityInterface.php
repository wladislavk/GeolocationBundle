<?php
namespace VKR\GeolocationBundle\Interfaces;

interface GeolocatableEntityInterface
{
    /**
     * @return float
     */
    public function getLat();

    /**
     * @return float
     */
    public function getLng();
}
