<?php

namespace App\Repository;

use App\Entity\Exercise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExerciseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exercise::class);
    }

    public function save(Exercise $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Exercise $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.orderNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByDatabaseType(string $databaseType): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.databaseType = :databaseType')
            ->setParameter('databaseType', $databaseType)
            ->orderBy('e.orderNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
