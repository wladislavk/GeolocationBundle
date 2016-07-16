<?php
namespace VKR\GeolocationBundle\Entity\Perishable;

use VKR\GeolocationBundle\Exception\MalformedBoundingBoxException;

class BoundingBox
{
    /**
     * @var array
     */
    public $latPair;

    /**
     * @var array
     */
    public $lngPairs;

    public function __construct($latPair, $lngPairs)
    {
        $exceptionType = '';
        if (!isset($latPair['min']) || !isset($latPair['max'])) {
            $exceptionType = 'latitude';
        }
        if (!sizeof($lngPairs)) {
            $exceptionType = 'longitude';
        }
        foreach ($lngPairs as $lngPair) {
            if (!isset($lngPair['min']) || !isset($lngPair['max'])) {
                $exceptionType = 'longitude';
            }
        }
        if ($exceptionType) {
            throw new MalformedBoundingBoxException($exceptionType);
        }
        $this->latPair = $latPair;
        $this->lngPairs = $lngPairs;
    }
}
