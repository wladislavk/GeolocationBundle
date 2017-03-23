<?php
namespace VKR\GeolocationBundle\Services;

use Doctrine\ORM\EntityManager;
use VKR\GeolocationBundle\Entity\Perishable\BoundingBox;
use VKR\GeolocationBundle\Exception\InterfaceNotImplementedException;
use VKR\GeolocationBundle\Interfaces\GeolocatableEntityInterface;

class DoctrineQuerier
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param BoundingBox $boundingBox
     * @param string $entityClassName
     * @return array|null
     */
    public function getRecords(BoundingBox $boundingBox, $entityClassName)
    {
        $this->checkEntityInterface($entityClassName);
        $conditions = $this->setBoundingBoxConditions($boundingBox);
        $query = "SELECT a FROM $entityClassName a WHERE " . $conditions;
        $dqlQuery = $this->entityManager->createQuery($query);
        $result = $dqlQuery->getResult();
        return $result;
    }

    /**
     * @param BoundingBox $boundingBox
     * @param string $entityClassName
     * @param string $fieldName Doctrine field name returned by SELECT DISTINCT
     * @return array
     */
    public function getDistinctRecords(BoundingBox $boundingBox, $entityClassName, $fieldName)
    {
        $this->checkEntityInterface($entityClassName);
        $conditions = $this->setBoundingBoxConditions($boundingBox);
        $query = "SELECT DISTINCT a.$fieldName FROM $entityClassName a WHERE " . $conditions;
        $dqlQuery = $this->entityManager->createQuery($query);
        $result = $dqlQuery->getScalarResult();
        if (!$result) {
            return [];
        }
        return array_column($result, $fieldName);
    }

    /**
     * @param BoundingBox $boundingBox
     * @return string
     */
    protected function setBoundingBoxConditions(BoundingBox $boundingBox)
    {
        $conditions = [];
        $conditions[] = "a.lat >= {$boundingBox->latPair['min']}";
        $conditions[] = "a.lat <= {$boundingBox->latPair['max']}";
        $lngConditions = [];
        foreach ($boundingBox->lngPairs as $lngPair) {
            $lngConditions[] = "(a.lng >= {$lngPair['min']} AND a.lng <= {$lngPair['max']})";
        }
        $conditions[] = "(" . implode(' OR ', $lngConditions) . ")";
        return implode(' AND ', $conditions);
    }

    private function checkEntityInterface($entityClassName)
    {
        $reflection = new \ReflectionClass($entityClassName);
        $interfaceName = GeolocatableEntityInterface::class;
        if ($reflection->implementsInterface($interfaceName) !== true) {
            throw new InterfaceNotImplementedException($entityClassName, $interfaceName);
        }
    }
}
