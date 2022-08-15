<?php

namespace App\Tests;

use App\Command\MercedezLoaderCommand;
use App\Entity\Car;
use App\Service\MercedesResponseTransformer;
use App\Service\MercedezClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;

class MercedezLoaderCommandTest extends KernelTestCase
{
    public function testCommand()
    {
        self::bootKernel();
        $container           = self::getContainer();
        $client              = $container->get(MercedezClient::class);
        $responseTransformer = $container->get(MercedesResponseTransformer::class);
        $em                  = $container->get('doctrine.orm.entity_manager');
        $command             = new MercedezLoaderCommand($responseTransformer, $client, $em);
        
        $command->run($this->createStub(Input::class), $this->createStub(Output::class));

        $car = $em->getRepository(Car::class)->findAll();

        self::assertCount(1, $car);
    }
}