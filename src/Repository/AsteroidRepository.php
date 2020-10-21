<?php

namespace App\Repository;

use App\Entity\Asteroid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AsteroidRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asteroid::class);
    }

    public function findHazardousQuery()
    {
        return $this
            ->createQueryBuilder('a')
            ->where('a.hazardous = TRUE')
            ->getQuery();
    }

    public function findFastest(bool $hazardous)
    {
        $maxQuery = $this->createQueryBuilder('at')
            ->select('MAX(at.speed)')
            ->where('at.hazardous = :hazardous')
            ->getDQL();

        return $this
            ->createQueryBuilder('a')
            ->where('a.speed = (' . $maxQuery . ')')
            ->andWhere('a.hazardous = :hazardous')
            ->setMaxResults(1)
            ->setParameter('hazardous', $hazardous)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
