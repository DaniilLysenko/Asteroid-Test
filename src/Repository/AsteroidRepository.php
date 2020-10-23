<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Asteroid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
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
            ->getQuery()
        ;
    }

    public function findFastest(bool $hazardous)
    {
        $maxQuery = $this
            ->createQueryBuilder('at')
            ->select('MAX(at.speed)')
            ->where('at.hazardous = :hazardous')
            ->getDQL()
        ;

        return $this
            ->createQueryBuilder('a')
            ->where('a.speed = ('.$maxQuery.')')
            ->andWhere('a.hazardous = :hazardous')
            ->setMaxResults(1)
            ->setParameter('hazardous', $hazardous)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

//    public function findBestMonth(bool $hazardous)
//    {
//        $connection = $this->getEntityManager()->getConnection();
//        $sql = 'SELECT MAX(ast.astCount) AS astMaxCount, ast.date FROM (SELECT COUNT(id) as astCount, `date` FROM asteroid WHERE hazardous = ? GROUP BY MONTH(`date`), YEAR(`date`)) AS ast';
//        $sql = $connection->prepare($sql);
//        $sql->bindValue(1, $hazardous, ParameterType::BOOLEAN);
//        $sql->execute();
//
//        return $sql->fetchAllAssociative();
//    }

    public function findBestMonth(bool $hazardous)
    {
        return $this
            ->createQueryBuilder('a')
            ->select('COUNT(a.id) as astCount, a.date, CONCAT(MONTH(a.date), YEAR(a.date)) AS monthYear')
            ->where('a.hazardous = :hazardous')
            ->groupBy('monthYear')
            ->setParameter('hazardous', $hazardous)
            ->getQuery()
            ->getResult()
        ;
    }
}
