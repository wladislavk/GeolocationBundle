About
=====

This bundle was created to sort out a rather nasty problem I've encountered - searching
for a point in a large chunk of data that is the nearest one to some point N. There is a
rather complicated formula that helps us do it precisely, however, it's a really bad idea
to use it in your DB query - you will kill your database.

This bundle provides a solution for this problem by creating a two-step algorithm that
first executes a simple DB query that looks for any point that has a remote chance
to be the nearest one and then using the script to apply the formula and choose a 'winner'
among these points.

The bundle consists of three services that you can use separately - first for step #1,
second for step #2, third for talking to Doctrine. The bundle depends on Symfony and
Doctrine, however, you do not need to actually use Doctrine if you prefer not to
activate the third service.

Installation
============

Nothing to install here, except for activating the bundle in ```AppKernel.php```.

Usage
=====

Retrieving a bounding box
-------------------------

First of all, we will need to create a bounding box which is a square chunk of land, and
then find all points that lie inside that box.

I assume that all your geospatial data uses degrees of latitude and longitude.

To create a bounding box, we need to know the side of the square. As all our data is in
degrees, we will use degrees for that measure as well. However, a degree of longitude
corresponds to different distances, depending on the latitude. Therefore, we will measure
the box in degrees of latitude, that are immutable. One degree of latitude equals
111.375 km.

Here is how the box is created around the point with given latitude and longitude.

```
$boundingBoxFinder = $this->get('vkr_geolocation.bounding_box_finder');
        $lat = 40;
        $lng = -100;
        $allowance = 0.5;
        $boundingBox = $this->boundingBoxFinder->setBoundingBox($lat, $lng, $allowance);
```

Here we have a square with a side of roughly 55 km (half a degree of latitude) with a
point (40, -100) at the center.

The desired allowance depends on how densely the points are distributed in your data source.
For example, if you are searching for zip codes in a metropolitan zone, you wouldn't
want to use anything more than 0.1 for allowance.

If your coordinates are located near the international date line (longitude of +180/-180),
the service will take that into account and will still give you a perfectly square box.

If you prefer not to use the ```BoundingBoxFinder``` service, you can create the
```BoundingBox``` object yourself, according to the following rules:
- ```lat``` property consists of an array with ```min``` and ```max``` values;
- ```lng``` property consists of an array with one or more arrays of ```min```
and ```max``` values.

Example:

```
$latPair = [
    'min' => 30,
    'max' => 35,
];
$lngPairs = [
    [
        'min' => '-100',
        'max' => '-95',
    ],
];
$boundingBox = new VKR\GeolocationBundle\Entity\Perishable\BoundingBox($latPair, $lngPairs);
```

Querying the DB
---------------

With the bounding box ready, we need to parse its contents into a DQL query. That is
what the ```doctrine_querier``` service is for. However, you need to define a data source
entity to use it. The entity must conform to ```VKR\GeolocationBundle\Interfaces\GeolocatableEntityInterface```
that defines ```getLat()``` and ```getLng()``` methods. The service will collect all
points that lie inside your bounding box.

```
$doctrineQuerier = $this->get('vkr_geolocation.doctrine_querier');
$result = $doctrineQuerier->getRecords($boundingBox, YourEntity::class);
```

The contents of ```$result``` are nothing more than what you usually get from
```getResult()``` in Doctrine.

Sometimes you might want to get a list of all values of a non-unique field that are
encountered in a bounding box. For example, you have a DB with zip codes but you are
only interested in cities. If this is the case, you can utilize ```getDistinctRecords()```:

```
$result = $doctrineQuerier->getDistinctRecords($boundingBox, YourEntity::class, 'city');
```

You will get a zero-indexed array of city names.

Calculating the nearest point
-----------------------------

Finally, we can test which point inside your query result is the nearest one.

```
$calculator = $this->get('vkr_geolocation.nearest_point_calculator');
$index = $calculator->findNearestPoint($lat, $lng, $result);
$nearestPointCoords = [
    'lat' => $result[$index]->getLat(),
    'lng' => $result[$index]->getLng(),
];
```

That's about it.

API
===

*VKR\GeolocationBundle\Entity\Perishable\BoundingBox BoundingBoxFinder::setBoundingBox(float $lat, float $lng, float $allowanceLat)*

*void DoctrineQuerier::__construct(Doctrine\ORM\EntityManager $em)*

*array|null DoctrineQuerier::getRecords(VKR\GeolocationBundle\Entity\Perishable\BoundingBox $boundingBox, string $entityClassName)*

*array DoctrineQuerier::getDistinctRecords(VKR\GeolocationBundle\Entity\Perishable\BoundingBox $boundingBox, string $entityClassName, string $fieldName)*

*int NearestPointCalculator::findNearestPoint(float $lat, float $lng, array $valueList)*

The third argument must be a zero-indexed array of ```VKR\GeolocationBundle\Interfaces\GeolocatableEntityInterface```
objects.
