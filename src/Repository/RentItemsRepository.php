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

public function freeItems($date, $objekt, $pax, $category, $resId)
{ $conn = $this->getEntityManager()->getConnection();
    if($resId>0){
       
        $sql = 'SELECT item.usetime, item.pax, item.objekt_id, item.category_id, item.name, o.id as objekt_id, item.id as item_id
                FROM rent_items as item
                JOIN objekt as o ON item.objekt_id = o.id
                WHERE item.objekt_id = :objekt
                AND item.pax >= :pax
                AND item.category_id = :category
                AND item.id NOT IN (
                    SELECT item_id FROM reservation res
                    WHERE (
                        res.kommen <= :date + INTERVAL item.usetime MINUTE 
                        AND res.gehen > :date
                        AND res.id != :resId
                    ) OR (
                        res.kommen >= :date 
                        AND res.kommen < :date + INTERVAL item.usetime MINUTE
                        AND res.id != :resId
                    )
                    
                )
                ORDER BY item_id';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'objekt' => $objekt,
            'date' => $date,
            'category' => $category,
            'pax' => $pax,
            'resId' => $resId
        ]);
        
    }
    else{
        $sql = 'SELECT item.usetime, item.pax, item.objekt_id, item.category_id, item.name, o.id as objekt_id, item.id as item_id
            FROM rent_items as item
            JOIN objekt as o ON item.objekt_id = o.id
            WHERE item.objekt_id = :objekt
            AND item.pax >= :pax
            AND item.category_id = :category
            AND item.id NOT IN (
                SELECT item_id FROM reservation res
                WHERE (
                    res.kommen <= :date + INTERVAL item.usetime MINUTE 
                    AND res.gehen > :date
                ) OR (
                    res.kommen >= :date 
                    AND res.kommen < :date + INTERVAL item.usetime MINUTE
                )
                
            )
            ORDER BY item_id';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'objekt' => $objekt,
            'date' => $date,
            'category' => $category,
            'pax' => $pax
        ]);
    }
    $datas = $resultSet->fetchAllAssociative();
        return $datas;
}

//wählt freies Itam für die Reseditervierung aus
public function selectFreeItemsEdit($kommen, $gehen, $objekt, $pax, $category,$resId)
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = 'SELECT item.usetime, item.pax, item.objekt_id, item.category_id, o.name, o.id as objekt_id, item.id as item_id
            FROM rent_items as item
            JOIN objekt as o ON item.objekt_id = o.id
            WHERE item.objekt_id = :objekt
            AND item.pax >= :pax
            AND item.category_id = :category
            AND item.id NOT IN (
                SELECT item_id 
                FROM reservation res 
                WHERE (
                    res.kommen <= :gehen 
                    AND res.gehen > :kommen
                    AND res.id != :resId
                ) OR (
                    res.kommen >= :kommen 
                    AND res.kommen < :gehen
                    AND res.id != :resId
                )
            )
            ORDER BY item.pax';
    $stmt = $conn->prepare($sql);
    $resultSet = $stmt->executeQuery([
        'objekt' => $objekt,
        'kommen' => $kommen,
        'gehen' => $gehen,
        'resId' => $resId,
        'category' => $category,
        'pax' => $pax
    ]);
    $datas = $resultSet->fetchAllAssociative();
    return $datas;
}
public function selectFreeItems($kommen, $gehen, $objekt, $pax, $category)
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = 'SELECT item.usetime, item.pax, item.objekt_id, item.category_id, o.name, o.id as objekt_id, item.id as item_id
            FROM rent_items as item
            JOIN objekt as o ON item.objekt_id = o.id
            WHERE item.objekt_id = :objekt
            AND item.pax >= :pax
            AND item.category_id = :category
            AND item.id NOT IN (
                SELECT item_id 
                FROM reservation res 
                WHERE (
                    res.kommen <= :gehen 
                    AND res.gehen > :kommen
                    AND res.aktiv is null
                ) OR (
                    res.kommen >= :kommen 
                    AND res.kommen < :gehen
                    AND res.aktiv is null
                )
            )
            ORDER BY item.pax';
    $stmt = $conn->prepare($sql);
    $resultSet = $stmt->executeQuery([
        'objekt' => $objekt,
        'kommen' => $kommen,
        'gehen' => $gehen,
        'category' => $category,
        'pax' => $pax
    ]);
    $datas = $resultSet->fetchAllAssociative();
    return $datas;
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
