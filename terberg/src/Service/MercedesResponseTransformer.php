<?php

namespace App\Service;

use App\Entity\Car;

class MercedesResponseTransformer
{
    private const MAKE = 'Mercedez Benz';

    public function createCarsFromResponse(array $cars)
    {
        foreach($cars as &$car) {
            $car = (new Car())
                ->setMake(self::MAKE)
                ->setModel($car['name'])
                ->setCatalogPrice(bcmul($car['priceInformation']['price'], 100))
            ;
        }

        return $cars;
    }
}