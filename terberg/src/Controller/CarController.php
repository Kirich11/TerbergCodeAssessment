<?php

namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CarController
{
    private const RESPONSE_START = '{"cars": [';
    private const RESPONSE_END   = ']}';

    private EntityManager $em;
    private ValidatorInterface $validator;


    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->validator = $validator;
    }

    #[Route('/car', 'createCar', methods: 'POST')]
    public function createCar(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $car = (new Car())
            ->setMake($data['make'] ?? null)
            ->setModel($data['model'] ?? null)
            ->setCatalogPrice($data['catalogPrice'] ?? null)
        ;

        $errors = $this->validator->validate($car);

        if (count($errors) > 0) {
            return new JsonResponse(['errors' => array_map(fn($error) => $error->message, $errors)], 422);
        }

        /** @var CarRepository $carRepo */
        $carRepo = $this->em->getRepository(Car::class);

        try {
            $carRepo->add($car, true);
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['errors' => [sprintf('Car "%s" "%s" already exists', $data['make'], $data['model'])]], 422);
        }
        
        return new JsonResponse(['carId' => $car->getId()]);
    }

    #[Route('/car', 'getAllCars')]
    public function getAllCars(Request $request): StreamedResponse
    {
        $carsRepo = $this->em->getRepository(Car::class);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($carsRepo) {
            $total = $carsRepo->count([]);
            echo self::RESPONSE_START;
            for ($i=0; $i < $total; $i = $i+CarRepository::DEFAULT_LIMIT) {
                $cars = $carsRepo->findAllWithLimitOffset(CarRepository::DEFAULT_LIMIT, $i);
                $output = json_encode($cars);
                echo preg_replace('/\[|\]/', '',$output);
                flush();
            }
            echo self::RESPONSE_END;
        });

        return $response->send();
    }

    #[Route('/car/lease/{duration}/{mileage}', 'getCarsForLease', requirements: ['duration' => '\d+', 'mileage' => '\d+'])]
    public function getCarsForLease(Request $request, int $duration, int $mileage): StreamedResponse
    {
        if ($duration <= 0 || $duration === null) {
            return new JsonResponse(['error' => 'duration must be an integer equal or greater than 1'], 422);
        }
        if ($mileage <= 0 || $mileage === null) {
            return new JsonResponse(['error' => 'mileage must be an integer equal or greater than 1'], 422);
        }

        $carsRepo = $this->em->getRepository(Car::class);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($carsRepo, $duration, $mileage) {
            $total = $carsRepo->count([]);
            echo self::RESPONSE_START;
            for ($i=0; $i < $total; $i = $i+CarRepository::DEFAULT_LIMIT) {
                $cars = $carsRepo->findForLeaseWithLimitOffset($duration, $mileage, CarRepository::DEFAULT_LIMIT, $i);
                $output = json_encode($cars);
                echo preg_replace('/\[|\]/', '',$output);
                flush();
            }
            echo self::RESPONSE_END;
        });

        return $response->send();
    }
}