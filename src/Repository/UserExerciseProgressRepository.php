<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserExerciseProgress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserExerciseProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserExerciseProgress::class);
    }

    public function save(UserExerciseProgress $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserExerciseProgress $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUserAndExercise(User $user, int $exerciseId): ?UserExerciseProgress
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.exercise = :exerciseId')
            ->setParameter('user', $user)
            ->setParameter('exerciseId', $exerciseId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findUserProgress(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.exercise', 'e')
            ->addSelect('e')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.orderNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
