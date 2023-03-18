<?php

namespace App\Repository;

use App\Entity\Objekt;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Objekt>
 *
 * @method Objekt|null find($id, $lockMode = null, $lockVersion = null)
 * @method Objekt|null findOneBy(array $criteria, array $orderBy = null)
 * @method Objekt[]    findAll()
 * @method Objekt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ObjektRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Objekt::class);
    }

    public function save(Objekt $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Objekt $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Objekt[] Returns an array of Objekt objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }
public function findAllfree( $date, $time,  $pax, $category)
{
    $conn = $this->getEntityManager()->getConnection();
   $sql =  'SELECT * from objekt where categories_id = :category AND id  IN
            (SELECT  o.id 
            From rent_items as item ,objekt as o 
            WHERE  item.objekt_id = o.id   
            AND item.pax >= :pax
            AND item.usetime
            AND item.id NOT IN
            (SELECT item_id FROM reservation res
            where 
            res.kommen > :date  AND
            res.gehen < :date + interval item.usetime minute     
            or
            res.kommen <= :date AND
            res.gehen > :date + interval item.usetime minute     
            )) ';
    $stmt = $conn->prepare($sql);
    $resultSet = $stmt->executeQuery(['time' => $time, 'date' => $date,  'pax' =>$pax, 'category' => $category]); 
    $datas = $resultSet->fetchAllAssociative();
    return $datas ;
}
public function findMy(User $user)
{
    $objekt = $user->getObjekt();
    if ($objekt) {
        return $objekt;
    }

    $company = $user->getCompany();
    if ($company) {
        return $this->findBy(['company' => $company]);
    }

    $companies = $user->getCompanies();
    if ($companies) {
        $objekts = [];
        foreach ($companies as $company) {
            $objekts = array_merge($objekts, $this->findBy(['company' => $company]));
        }
        return $objekts;
    }

    return $this->findAll();
}

public function findRecomodation($objekt) 
    {
        $conn = $this->getEntityManager()->getConnection();
   $sql =  'SELECT points, id from reservation where item_id  IN
            (SELECT  item.id 
            From rent_items as item ,objekt as o 
            WHERE  item.objekt_id = o.id   
            AND o.id= :objekt
            ) ';
    $stmt = $conn->prepare($sql);
    $resultSet = $stmt->executeQuery(['objekt' => $objekt]); 
    $datas = $resultSet->fetchAllAssociative();
    return $datas ;
}
public function findOneById($value): ?Objekt
        {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :val')
            ->setParameter('val', $value)
           ->getQuery()
           ->getOneOrNullResult()
                   ;
}
}
