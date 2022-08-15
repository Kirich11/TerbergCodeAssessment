<?php

namespace App\Entity;

use App\Repository\CarRepository;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CarRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_car_make_model', columns: ['make', 'model'])]
class Car implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $make = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $model = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $catalogPrice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMake(): ?string
    {
        return $this->make;
    }

    public function setMake(string $make): self
    {
        $this->make = $make;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getCatalogPrice(): ?int
    {
        return $this->catalogPrice;
    }

    public function setCatalogPrice(int $catalogPrice): self
    {
        $this->catalogPrice = $catalogPrice;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id'           => $this->id,
            'make'         => $this->make,
            'model'        => $this->model,
            'catalogPrice' => $this->catalogPrice,
        ];
    }
}
