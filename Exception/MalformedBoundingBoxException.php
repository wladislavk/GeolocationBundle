<?php
namespace VKR\GeolocationBundle\Exception;

class MalformedBoundingBoxException extends \Exception
{
    public function __construct($type)
    {
        $message = "Malformed $type in bounding box";
        parent::__construct($message);
    }
}
