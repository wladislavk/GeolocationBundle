<?php
namespace VKR\GeolocationBundle\TestHelpers;

use VKR\GeolocationBundle\Entity\Perishable\BoundingBox;
use VKR\GeolocationBundle\Services\DoctrineQuerier;

class DoctrineQuerierChild extends DoctrineQuerier
{
    public function setBoundingBoxConditions(BoundingBox $boundingBox)
    {
        return parent::setBoundingBoxConditions($boundingBox);
    }
}
