<?php

namespace App\Command;

use App\Service\MercedesResponseTransformer;
use App\Service\MercedezClient;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'mercedez:load',
    description: 'Load mercedez car models.',
    hidden: false,
    aliases: ['mercedez:load']
)]
class MercedezLoaderCommand extends Command
{
    private const BATCH_SIZE = 10000;

    private MercedezClient $client;
    private MercedesResponseTransformer $transformer;

    public function __construct(
        MercedesResponseTransformer $transformer,
        MercedezClient $client,
        EntityManagerInterface $em
    ) {
        $this->client      = $client;
        $this->transformer = $transformer;
        $this->em          = $em;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = $this->client->getCars();

        $cars = $this->transformer->createCarsFromResponse($response);

        while($cars != []) {
            $carsBatch = \array_splice($cars, 0, self::BATCH_SIZE); 
            $this->em->transactional(function () use ($carsBatch) {
                foreach($carsBatch as $car) {
                    try {
                        $this->em->persist($car);
                    } catch (UniqueConstraintViolationException $e) {
                        //do nothing, just skip the car
                    }
                }
            });
            $this->em->clear();
        }

        return 0;
    }
}