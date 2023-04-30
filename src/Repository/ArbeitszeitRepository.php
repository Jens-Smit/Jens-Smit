<?php

namespace App\Repository;

use App\Entity\Arbeitszeit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Arbeitszeit>
 *
 * @method Arbeitszeit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Arbeitszeit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Arbeitszeit[]    findAll()
 * @method Arbeitszeit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArbeitszeitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Arbeitszeit::class);
    }

    public function save(Arbeitszeit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Arbeitszeit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Arbeitszeit[] Returns an array of Arbeitszeit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Arbeitszeit
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
