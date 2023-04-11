<?php

namespace App\Repository;

use App\Entity\Arbeitsbereiche;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Arbeitsbereiche>
 *
 * @method Arbeitsbereiche|null find($id, $lockMode = null, $lockVersion = null)
 * @method Arbeitsbereiche|null findOneBy(array $criteria, array $orderBy = null)
 * @method Arbeitsbereiche[]    findAll()
 * @method Arbeitsbereiche[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArbeitsbereicheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Arbeitsbereiche::class);
    }

    public function save(Arbeitsbereiche $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Arbeitsbereiche $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Arbeitsbereiche[] Returns an array of Arbeitsbereiche objects
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

//    public function findOneBySomeField($value): ?Arbeitsbereiche
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
