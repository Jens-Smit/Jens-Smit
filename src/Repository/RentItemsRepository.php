<?php

namespace App\Repository;

use App\Entity\RentItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RentItems>
 *
 * @method RentItems|null find($id, $lockMode = null, $lockVersion = null)
 * @method RentItems|null findOneBy(array $criteria, array $orderBy = null)
 * @method RentItems[]    findAll()
 * @method RentItems[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RentItemsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RentItems::class);
    }

    public function save(RentItems $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RentItems $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return RentItems[] Returns an array of RentItems objects
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
public function findAllfree( $date, $time,  $pax){
    $conn = $this->getEntityManager()->getConnection();
   $sql =  'SELECT  item.usetime,item.pax, item.objekt_id, item.category_id ,o.name, o.id as objekt_id, item.id as item_id
            From rent_items as item ,objekt as o 
            WHERE  item.objekt_id = objekt_id   
            AND item.pax >= :pax
            AND item.usetime
            AND item.id NOT IN
            (SELECT item_id FROM reservation res
            where 
            res.kommen > :date + interval item.usetime minute AND
            res.gehen < :date + interval item.usetime minute     
            or
            res.kommen <= :date AND
            res.gehen > :date      
            )order by
            item_id';
    $stmt = $conn->prepare($sql);
$resultSet = $stmt->executeQuery(['time' => $time, 'date' => $date,  'pax' =>$pax]); 
$datas = $resultSet->fetchAllAssociative();
return $datas ;
}
public function findfree( $date, $time, $objekt, $pax){
    $conn = $this->getEntityManager()->getConnection();
   $sql =  'SELECT  item.usetime,item.pax, item.objekt_id, item.category_id ,o.name, o.id, item.id as item_id
            From rent_items as item ,objekt as o 
            WHERE  item.objekt_id = o.id 
            AND item.objekt_id = :objekt
            AND item.pax >= :pax
            AND item.usetime
            AND item.id NOT IN
            (SELECT item_id FROM reservation res
            where 
            res.kommen > :date + interval item.usetime minute AND
            res.gehen < :date + interval item.usetime minute     
            or
            res.kommen <= :date AND
            res.gehen > :date      
            )order by
            item_id';
    $stmt = $conn->prepare($sql);
$resultSet = $stmt->executeQuery(['time' => $time, 'date' => $date, 'objekt' =>$objekt, 'pax' =>$pax]); 
$datas = $resultSet->fetchAllAssociative();
return $datas ;
}
    public function findOneBySomeField($value): ?RentItems
    {
        return $this->createQueryBuilder('r')
           ->andWhere('r.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
