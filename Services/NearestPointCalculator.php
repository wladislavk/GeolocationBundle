<?php
namespace VKR\GeolocationBundle\Services;

use VKR\GeolocationBundle\Exception\InvalidGeolocationValueException;
use VKR\GeolocationBundle\Interfaces\GeolocatableEntityInterface;

class NearestPointCalculator
{
    const EARTH_RADIUS_IN_KM = 6371;

    /**
     * @param float $lat
     * @param float $lng
     * @param array $valueList
     * @return int
     */
    public function findNearestPoint($lat, $lng, array $valueList)
    {
        $minDistance = 100000; // as big as possible
        $minKey = 0;
        /**
         * @var int $index
         * @var GeolocatableEntityInterface $geolocatableEntity
         */
        foreach ($valueList as $index => $geolocatableEntity) {
            $distance = $this->getValueDistance($lat, $lng, $geolocatableEntity);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $minKey = $index;
            }
        }
        return $minKey;
    }

    /**
     * Helper function for interface type check
     *
     * @param float $lat
     * @param float $lng
     * @param GeolocatableEntityInterface $entity
     * @return float
     */
    protected function getValueDistance($lat, $lng, GeolocatableEntityInterface $entity)
    {
        return $this->getDistance($lat, $lng, $entity->getLat(), $entity->getLng());
    }

    /**
     * @param float $lat1deg
     * @param float $lng1deg
     * @param float $lat2deg
     * @param float $lng2deg
     * @return float
     * @throws InvalidGeolocationValueException
     */
    protected function getDistance($lat1deg, $lng1deg, $lat2deg, $lng2deg)
    {
        if ($lat1deg < -90 || $lat1deg > 90) {
            throw new InvalidGeolocationValueException($lat1deg, 'latitude');
        }
        if ($lat2deg < -90 || $lat2deg > 90) {
            throw new InvalidGeolocationValueException($lat2deg, 'latitude');
        }
        if ($lng1deg < -180 || $lng1deg > 180) {
            throw new InvalidGeolocationValueException($lng1deg, 'longitude');
        }
        if ($lng2deg < -180 || $lng2deg > 180) {
            throw new InvalidGeolocationValueException($lng2deg, 'longitude');
        }
        // DEGREE TO RADIAN
        $lat1rad = $lat1deg / 180 * pi();
        $lng1rad = $lng1deg / 180 * pi();
        $lat2rad = $lat2deg / 180 * pi();
        $lng2rad = $lng2deg / 180 * pi();
        // FORMULA: e = ARCCOS ( SIN(Latitude1) * SIN(Latitude2) + COS(Latitude1) * COS(Latitude2) * COS(Longitude2-Longitude1) ) * EARTH_RADIUS
        $distanceInKM = acos(
                sin($lat1rad) * sin($lat2rad) + cos($lat1rad) * cos($lat2rad) * cos($lng2rad - $lng1rad)
            ) * self::EARTH_RADIUS_IN_KM;
        return $distanceInKM;
    }
}
