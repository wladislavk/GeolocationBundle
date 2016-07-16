<?php
namespace VKR\GeolocationBundle\Services;

use VKR\GeolocationBundle\Entity\Perishable\BoundingBox;
use VKR\GeolocationBundle\Exception\InvalidGeolocationValueException;

class BoundingBoxFinder
{

    public function __construct()
    {
    }

    /**
     * @param float $lat Latitude in degrees
     * @param float $lng Longitude in degrees
     * @param float $allowanceLat Maximum allowed distance between the input and the result
     * in degrees of latitude
     * @return BoundingBox
     */
    public function setBoundingBox($lat, $lng, $allowanceLat)
    {
        $this->checkLatAndLngForValidity($lat, $lng);
        $allowanceLng = $this->calculateAllowanceLng($allowanceLat, $lat);
        $latPair = [
            'min' => $lat - $allowanceLat,
            'max' => $lat + $allowanceLat,
        ];
        $lngPairs = [
            [
                'min' => $lng - $allowanceLng,
                'max' => $lng + $allowanceLng,
            ],
        ];
        $latPair = $this->wrapLat($latPair);
        $lngPairs = $this->wrapLng($lngPairs);
        $boundingBox = new BoundingBox($latPair, $lngPairs);
        return $boundingBox;
    }

    /**
     * @param float $lat
     * @param float $lng
     * @throws InvalidGeolocationValueException
     */
    protected function checkLatAndLngForValidity($lat, $lng)
    {
        if ($lat > 90 || $lat < -90) {
            throw new InvalidGeolocationValueException($lat, 'latitude');
        }
        if ($lng < -180 || $lng > 180) {
            throw new InvalidGeolocationValueException($lng, 'longitude');
        }
    }

    /**
     * @param float $allowanceLat
     * @param float $lat
     * @return float
     */
    protected function calculateAllowanceLng($allowanceLat, $lat)
    {
        return round($allowanceLat * cos(deg2rad($lat)), 2);
    }

    /**
     * @param array $latPair
     * @return array
     */
    protected function wrapLat(array $latPair)
    {
        if ($latPair['max'] > 90) {
            $latPair['max'] = 90;
        }
        if ($latPair['min'] < -90) {
            $latPair['min'] = -90;
        }
        return $latPair;
    }

    /**
     * @param array $lngPairs
     * @return array
     */
    protected function wrapLng(array $lngPairs)
    {
        $lngPair = $lngPairs[0];
        if ($lngPair['min'] < -180) {
            $firstMinLng = 360 - (-1 * $lngPair['min']);
            $firstMaxLng = 180;
            $secondMinLng = -180;
            $secondMaxLng = $lngPair['max'];
            $lngPairs = [
                [
                    'min' => $firstMinLng,
                    'max' => $firstMaxLng,
                ],
                [
                    'min' => $secondMinLng,
                    'max' => $secondMaxLng,
                ]
            ];
        }
        if ($lngPair['max'] > 180) {
            $firstMinLng = $lngPair['min'];
            $firstMaxLng = 180;
            $secondMinLng = -180;
            $secondMaxLng = -360 + $lngPair['max'];
            $lngPairs = [
                [
                    'min' => $firstMinLng,
                    'max' => $firstMaxLng,
                ],
                [
                    'min' => $secondMinLng,
                    'max' => $secondMaxLng,
                ],
            ];
        }
        return $lngPairs;
    }

}
