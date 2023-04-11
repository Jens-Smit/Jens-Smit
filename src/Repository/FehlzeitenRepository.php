<?php

namespace App\Repository;

use App\Entity\Fehlzeiten;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fehlzeiten>
 *
 * @method Fehlzeiten|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fehlzeiten|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fehlzeiten[]    findAll()
 * @method Fehlzeiten[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FehlzeitenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fehlzeiten::class);
    }

    public function save(Fehlzeiten $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Fehlzeiten $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Fehlzeiten[] Returns an array of Fehlzeiten objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Fehlzeiten
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
