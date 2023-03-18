<?php

namespace App\Repository;


use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Reservation>
 *
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function save(Reservation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    

    public function remove(Reservation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findByObjekt_Date_Time($objekt)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql =  'SELECT *
          From reservation  
                 WHERE  item_id   IN
                 (SELECT item.id as objekt_id FROM rent_items as item
                 WHERE objekt_id = :objekt
                 
                )';
         $stmt = $conn->prepare($sql);
     $resultSet = $stmt->executeQuery(['objekt' =>$objekt]); 
     $datas = $resultSet->fetchAllAssociative();
     


     return $datas ;
    }

    public function findAllAtDateAndTime( $date, $time, $objekt, $pax){
            $conn = $this->getEntityManager()->getConnection();
           $sql =  'SELECT  item.pax, item.objekt_id, item.category_id ,o.name, o.id, item.id as item_id
                    From rent_items as item ,objekt as o 
                    WHERE  item.objekt_id = o.id 
                    AND item.objekt_id = :objekt
                    AND item.pax >= :pax
                    AND item.id NOT IN
                    (SELECT item_id FROM reservation as res
                    where 
                    res.kommen > :time AND
                    res.gehen < :time     
                    or
                    res.kommen <= :date AND
                    res.gehen > :date
                
                    
                    )';
            $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['time' => $time, 'date' => $date, 'objekt' =>$objekt, 'pax' =>$pax]); 
        $datas = $resultSet->fetchAllAssociative();
        


        return $datas ;

}
//    /**
//     * @return Reservation[] Returns an array of Reservation objects
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

//    public function findOneBySomeField($value): ?Reservation
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
