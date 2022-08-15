<?php

namespace App\Repository;

use App\Entity\Car;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Car>
 *
 * @method Car|null find($id, $lockMode = null, $lockVersion = null)
 * @method Car|null findOneBy(array $criteria, array $orderBy = null)
 * @method Car[]    findAll()
 * @method Car[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarRepository extends ServiceEntityRepository
{
    public const DEFAULT_LIMIT = 1000;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    public function add(Car $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Car $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findForLeaseWithLimitOffset(int $duration, int $mileage, int $limit = self::DEFAULT_LIMIT, int $offset = 0): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('c.make, c.model, c.catalogPrice / :duration * :mileage / 1500 as leasePrice')
            ->setParameters([
                'duration' => $duration,
                'mileage'  => $mileage
            ])
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
        ;

        return $query->getResult();
    }

    public function findAllWithLimitOffset(int $limit = self::DEFAULT_LIMIT, int $offset = 0): array
    {
        return $this->createQueryBuilder('c')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->getQuery()
                ->getResult()
            ;
    }
}
