<?php
namespace VKR\GeolocationBundle\Tests\Services;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use VKR\GeolocationBundle\Exception\InterfaceNotImplementedException;
use VKR\GeolocationBundle\TestHelpers\DoctrineQuerierChild;
use VKR\GeolocationBundle\TestHelpers\GeolocatableEntity;
use VKR\GeolocationBundle\Entity\Perishable\BoundingBox;

class DoctrineQuerierTest extends TestCase
{
    /**
     * @var DoctrineQuerierChild
     */
    private $doctrineQuerier;

    /**
     * @var BoundingBox
     */
    private $boundingBox;

    public function setUp()
    {
        $entityManager = $this->mockEntityManager();
        $this->doctrineQuerier = new DoctrineQuerierChild($entityManager);
        $this->boundingBox = new BoundingBox(
            [
                'min' => -5,
                'max' => 5,
            ],
            [
                [
                    'min' => 20,
                    'max' => 30,
                ],
            ]
        );
    }

    public function testGetRecords()
    {
        $result = $this->doctrineQuerier->getRecords($this->boundingBox, GeolocatableEntity::class);
        $this->assertEquals(2, sizeof($result));
        /** @var GeolocatableEntity $firstObject */
        $firstObject = $result[0];
        $this->assertEquals(20, $firstObject->getLat());
    }

    public function testGetDistinctRecords()
    {
        $result = $this->doctrineQuerier->getDistinctRecords($this->boundingBox, GeolocatableEntity::class, 'city');
        $this->assertEquals(2, sizeof($result));
        $this->assertEquals('Boston', $result[1]);
    }

    public function testInterfaceNotImplemented()
    {
        $this->expectException(InterfaceNotImplementedException::class);
        $this->doctrineQuerier->getRecords($this->boundingBox, \DateTime::class);
    }

    public function testBoundingBoxConditions()
    {
        $conditions = $this->doctrineQuerier->setBoundingBoxConditions($this->boundingBox);
        $expected = 'a.lat >= -5 AND a.lat <= 5 AND ((a.lng >= 20 AND a.lng <= 30))';
        $this->assertEquals($expected, $conditions);
    }

    public function testBoundingBoxConditionsWithWrap()
    {
        $this->boundingBox = new BoundingBox(
            [
                'min' => -5,
                'max' => 5,
            ],
            [
                [
                    'min' => 160,
                    'max' => 180,
                ],
                [
                    'min' => -180,
                    'max' => -170,
                ],
            ]
        );
        $conditions = $this->doctrineQuerier->setBoundingBoxConditions($this->boundingBox);
        $expected = 'a.lat >= -5 AND a.lat <= 5 AND ((a.lng >= 160 AND a.lng <= 180) OR (a.lng >= -180 AND a.lng <= -170))';
        $this->assertEquals($expected, $conditions);
    }

    private function mockEntityManager()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('createQuery')->willReturn($this->mockDoctrineQuery());
        return $entityManager;
    }

    private function mockDoctrineQuery()
    {
        $doctrineQuery = $this->createMock(AbstractQuery::class);
        $doctrineQuery->method('getResult')
            ->willReturnCallback([$this, 'getResultCallback']);
        $doctrineQuery->method('getScalarResult')
            ->willReturnCallback([$this, 'getScalarResultCallback']);
        return $doctrineQuery;
    }

    public function getResultCallback()
    {
        $value = [
            new GeolocatableEntity(20, 0),
            new GeolocatableEntity(1, 1),
        ];
        return $value;
    }

    public function getScalarResultCallback()
    {
        $value = [
            [
                'city' => 'New York',
                'country' => 'US',
            ],
            [
                'city' => 'Boston',
                'country' => 'US',
            ],
        ];
        return $value;
    }
}
