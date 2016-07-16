<?php
namespace VKR\GeolocationBundle\Exception;

class InvalidGeolocationValueException extends \Exception
{
    public function __construct($value, $type)
    {
        $type = ucfirst($type);
        $message = "$type value of $value is invalid";
        parent::__construct($message);
    }
}
