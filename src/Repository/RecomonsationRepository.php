<?php

namespace App\Repository;

use App\Entity\Recomonsation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recomonsation>
 *
 * @method Recomonsation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recomonsation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recomonsation[]    findAll()
 * @method Recomonsation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecomonsationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recomonsation::class);
    }

    public function save(Recomonsation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Recomonsation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findByObjekt($objekt){
        
    $conn = $this->getEntityManager()->getConnection();
       $sql =  'SELECT rec.points as points, rec.id as id 
                From  reservation as res ,recomonsation as rec ,rent_items as item ,objekt as o
                WHERE item.objekt_id = :objekt
                AND item.objekt_id = o.id
                AND item.id = res.item_id
                ';
        $stmt = $conn->prepare($sql);
    $resultSet = $stmt->executeQuery(['objekt' => $objekt]); 
    $datas = $resultSet->fetchAllAssociative();
    


    return $datas ;

}
//    /**
//     * @return Recomonsation[] Returns an array of Recomonsation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Recomonsation
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
