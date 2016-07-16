<?php
namespace VKR\GeolocationBundle\Tests\Services;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use VKR\GeolocationBundle\Exception\InterfaceNotImplementedException;
use VKR\GeolocationBundle\TestHelpers\DoctrineQuerierChild;
use VKR\GeolocationBundle\TestHelpers\GeolocatableEntity;
use VKR\GeolocationBundle\Entity\Perishable\BoundingBox;

class DoctrineQuerierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineQuerierChild
     */
    protected $doctrineQuerier;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineQuery;

    /**
     * @var BoundingBox
     */
    protected $boundingBox;

    public function setUp()
    {
        $this->mockDoctrineQuery();
        $this->mockEntityManager();
        $this->doctrineQuerier = new DoctrineQuerierChild($this->entityManager);
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
        $this->setExpectedException(InterfaceNotImplementedException::class);
        $result = $this->doctrineQuerier->getRecords($this->boundingBox, \DateTime::class);
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

    protected function mockEntityManager()
    {
        $this->entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityManager->expects($this->any())
            ->method('createQuery')
            ->will($this->returnValue($this->doctrineQuery));
    }

    protected function mockDoctrineQuery()
    {
        $this->doctrineQuery = $this
            ->getMockBuilder(AbstractQuery::class)
            ->setMethods(['getResult', 'getScalarResult'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->doctrineQuery->expects($this->any())
            ->method('getResult')
            ->will($this->returnCallback([$this, 'getResultCallback']));
        $this->doctrineQuery->expects($this->any())
            ->method('getScalarResult')
            ->will($this->returnCallback([$this, 'getScalarResultCallback']));
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
