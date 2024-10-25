<?php

namespace App\Repository;

use App\Entity\ItemCategoriesPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ItemCategoriesPrice>
 *
 * @method ItemCategoriesPrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemCategoriesPrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemCategoriesPrice[]    findAll()
 * @method ItemCategoriesPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemCategoriesPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemCategoriesPrice::class);
    }

    public function save(ItemCategoriesPrice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ItemCategoriesPrice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function getPricesForMonth($month, $year, $itemCategory)
    {
         
       
        $startDate = new \DateTime("$year-$month-01");
        $startDateStr=  $startDate->format('Y-m-d');
        $endDate =  clone $startDate;
        $endDate->modify('last day of this month');
        $endDateStr = $endDate->format('Y-m-d');
            $conn = $this->getEntityManager()->getConnection();
            $sql =  'SELECT  i.start, i.end, i.price
                     From item_categories_price as i 
                     WHERE   (i.item_category_id = :itemCategory 
                     AND i.start <= :startDate
                     AND i.end >= :startDate) 
                     OR (i.item_category_id = :itemCategory 
                     AND i.start <= :startDate
                     AND i.end <= :endDate)
                     OR (i.item_category_id = :itemCategory 
                     AND i.start >= :startDate
                     AND i.end <= :startDate)
                      OR (i.item_category_id = :itemCategory 
                     AND i.start >= :startDate
                     AND i.end >= :endDate)
                     OR (i.item_category_id = :itemCategory 
                     AND i.start >= :startDate
                     AND i.end <= :endDate)
                     order by
                     i.start';
            $stmt = $conn->prepare($sql);
            $resultSet = $stmt->executeQuery(['startDate' => $startDateStr, 'endDate' => $endDateStr,  'itemCategory' =>$itemCategory]); 
            $datas = $resultSet->fetchAllAssociative(); 

        return $datas;
    }
    public function updateOrCreatePrice($neuerPreis, $neuerStarttermin, $neuerEndtermin, $kategorieId)
    {
       
        
        $entityManager = $this->getEntityManager(); // Den EntityManager holen
    
        // Vorhandene Preise im Zeitraum finden
        $vorhandenePreise = $entityManager->createQueryBuilder()
            ->select('p') // Alle Spalten von "p" auswählen
            ->from('App\Entity\ItemCategoriesPrice', 'p') // Aus der Entity "ItemCategoriesPrice" mit Alias "p"
            ->where('p.ItemCategory = :kategorieId') // Wo die "itemCategory" gleich der angegebenen "kategorieId" ist
            ->andWhere('(
                (p.start <= :neuerStarttermin AND p.end >= :neuerStarttermin)
                OR (p.start < :neuerEndtermin AND p.end > :neuerEndtermin)
                OR (p.start >= :neuerStarttermin AND p.end <= :neuerEndtermin)
            )') // Wo das Start- oder Enddatum des vorhandenen Preises mit dem neuen Preisbereich überlappt
            ->setParameter('kategorieId', $kategorieId) // Den Wert der "kategorieId" an den Parameter binden
            ->setParameter('neuerStarttermin', $neuerStarttermin) // Den Wert des "neuerStarttermin" an den Parameter binden
            ->setParameter('neuerEndtermin', $neuerEndtermin) // Den Wert des "neuerEndtermin" an den Parameter binden
            ->getQuery() // Die Abfrage erstellen
            ->getResult(); // Die Ergebnisse abrufen
               
        // Vorhandene Preise basierend auf ihrer Überlappung mit dem neuen Preisbereich bearbeiten
   
        
        if (!empty($vorhandenePreise)) {
         
            $counter = false;
            foreach ($vorhandenePreise as $vorhandenerPreis) {
                $neuerStartterminMinus = clone $neuerStarttermin;
                $neuerStartterminMinus->modify('-1 day');
                $neuerEndterminPlus = clone $neuerEndtermin;
                $neuerEndterminPlus->modify('+1 day');
                
              //prüfen ob der neue termin einen alten termin beinhaltet
                if ($vorhandenerPreis->getStart() >= $neuerStarttermin && $vorhandenerPreis->getEnd() <= $neuerEndtermin) {
                    if($counter === false){
                    // Neuer Preis überschneidet vorhandenen Preis vollständig: Vorhandenen Preis aktualisieren
                    $vorhandenerPreis->setPrice($neuerPreis); // Den Preis des vorhandenen Preises auf den neuen Preis setzen
                    $vorhandenerPreis->setStart($neuerStarttermin); // Den Starttermin des vorhandenen Preises auf den neuen Starttermin setzen
                    $vorhandenerPreis->setEnd($neuerEndtermin); // Den Endtermin des vorhandenen Preises auf den neuen Endtermin setzen
                    $counter = true; 
              
                    }else{
                    $entityManager->remove($vorhandenerPreis);
                 
                    }
                //prüfen ob der neue termin in einem alten termin anfäng    
                } else if ($vorhandenerPreis->getStart() < $neuerStarttermin && $vorhandenerPreis->getEnd() < $neuerEndtermin) {
                    // Neuer Preis beginnt innerhalb des vorhandenen Preises: Vorhandenen Preis aktualisieren, neuen Preis erstellen
                    
                        $vorhandenerPreis->setEnd($neuerStartterminMinus); // Den Endtermin des vorhandenen Preises auf den neuen Starttermin setzen
                    if($counter === false){  
                        $neuerPreisDatensatz = new ItemCategoriesPrice(); // Neuen Datensatz für den neuen Preis erstellen
                        $neuerPreisDatensatz->setItemCategory($kategorieId); // Die "itemCategory" des neuen Datensatzes auf die "kategorieId" setzen
                        $neuerPreisDatensatz->setPrice($neuerPreis); // Den Preis des neuen Datensatzes auf den Preis des vorhandenen Preises setzen
                        $neuerPreisDatensatz->setStart($neuerStarttermin); // Den Starttermin des neuen Datensatzes auf den neuen Starttermin setzen
                        $neuerPreisDatensatz->setEnd($neuerEndtermin); // Den Endtermin des neuen Datensatzes auf den Endtermin des vorhandenen Preises setzen
                       
                        $entityManager->persist($neuerPreisDatensatz); // Den neuen Datensatz zur Persistenz hinzufügen
                        $counter  =true ;
                
                    }
                } else if ($vorhandenerPreis->getStart() > $neuerStarttermin && $vorhandenerPreis->getEnd() > $neuerEndtermin) {
                    // Neuer Preis endet innerhalb des vorhandenen Preises: Vorhandenen Preis aktualisieren, neuen Preis erstellen
                    $vorhandenerPreis->setStart($neuerEndterminPlus); // Den Starttermin des vorhandenen Preises auf den neuen Endtermin setzen
                    if($counter === false){ 
                    $neuerPreisDatensatz = new ItemCategoriesPrice(); // Neuen Datensatz für den neuen Preis erstellen
                    $neuerPreisDatensatz->setItemCategory($kategorieId); // Die "itemCategory" des neuen Datensatzes auf die "kategorieId" setzen
                    $neuerPreisDatensatz->setPrice($neuerPreis); // Den Preis des neuen Datensatzes auf den Preis des vorhandenen Preises setzen
                    $neuerPreisDatensatz->setStart($neuerStarttermin); // Den Starttermin des neuen Datensatzes auf den neuen Starttermin setzen // **Füge $neuerStarttermin ein**
                    $neuerPreisDatensatz->setEnd($neuerEndtermin); // Den Endtermin des neuen Datensatzes auf den neuen Endtermin setzen
                  
                    $entityManager->persist($neuerPreisDatensatz); // Den neuen Datensatz zur Persistenz hinzufügen
                    $counter  =true ;
                  
                    }
                } else if ($vorhandenerPreis->getStart() < $neuerStarttermin && $vorhandenerPreis->getEnd() > $neuerEndtermin) {
                    // Neuer Preis überschneidet vorhandenen Preis teilweise: Vorhandenen Preis aktualisieren
                    $neuerPreisDatensatz = new ItemCategoriesPrice(); // Neuen Datensatz für den neuen Preis erstellen
                    $neuerPreisDatensatz->setItemCategory($kategorieId); // Die "itemCategory" des neuen Datensatzes auf die "kategorieId" setzen
                    $neuerPreisDatensatz->setPrice($vorhandenerPreis->getPrice()); // Den Preis des neuen Datensatzes auf den neuen Preis setzen
                    $neuerPreisDatensatz->setStart($neuerEndterminPlus); // Den Starttermin des neuen Datensatzes auf den neuen Starttermin setzen
                    $neuerPreisDatensatz->setEnd($vorhandenerPreis->getEnd()); // Den Endtermin des neuen Datensatzes auf den neuen Endtermin setzen
                    $entityManager->persist($neuerPreisDatensatz);
                    $vorhandenerPreis->setEnd($neuerStartterminMinus);
                    if($counter === false){
                        $neuerPreisDatensatz = new ItemCategoriesPrice(); // Neuen Datensatz für den neuen Preis erstellen
                        $neuerPreisDatensatz->setItemCategory($kategorieId); // Die "itemCategory" des neuen Datensatzes auf die "kategorieId" setzen
                        $neuerPreisDatensatz->setPrice($neuerPreis); // Den Preis des neuen Datensatzes auf den neuen Preis setzen
                        $neuerPreisDatensatz->setStart($neuerStarttermin); // Den Starttermin des neuen Datensatzes auf den neuen Starttermin setzen
                        $neuerPreisDatensatz->setEnd($neuerEndtermin); // Den Endtermin des neuen Datensatzes auf den neuen Endtermin setzen
                        $entityManager->persist($neuerPreisDatensatz);
                        $counter = true;
                      
                    }
                } else {
                    if($counter === false){
                    // Neuer Preis überschneidet keinen vorhandenen Preis: Neuen Preis erstellen
                    $neuerPreisDatensatz = new ItemCategoriesPrice(); // Neuen Datensatz für den neuen Preis erstellen
                    $neuerPreisDatensatz->setItemCategory($kategorieId); // Die "itemCategory" des neuen Datensatzes auf die "kategorieId" setzen
                    $neuerPreisDatensatz->setPrice($neuerPreis); // Den Preis des neuen Datensatzes auf den neuen Preis setzen
                    $neuerPreisDatensatz->setStart($neuerStarttermin); // Den Starttermin des neuen Datensatzes auf den neuen Starttermin setzen
                    $neuerPreisDatensatz->setEnd($neuerEndtermin); // Den Endtermin des neuen Datensatzes auf den neuen Endtermin setzen
                    $entityManager->persist($neuerPreisDatensatz); // Den neuen Datensatz zur Persistenz hinzufügen
                     $counter  =true ;        
                    }
                }  
            }
            $entityManager->flush(); // Alle Änderungen in der Datenbank speichern
        }else{

                    $neuerPreisDatensatz = new ItemCategoriesPrice(); // Neuen Datensatz für den neuen Preis erstellen
                    $neuerPreisDatensatz->setItemCategory($kategorieId); // Die "itemCategory" des neuen Datensatzes auf die "kategorieId" setzen
                    $neuerPreisDatensatz->setPrice($neuerPreis); // Den Preis des neuen Datensatzes auf den neuen Preis setzen
                    $neuerPreisDatensatz->setStart($neuerStarttermin); // Den Starttermin des neuen Datensatzes auf den neuen Starttermin setzen
                    $neuerPreisDatensatz->setEnd($neuerEndtermin); // Den Endtermin des neuen Datensatzes auf den neuen Endtermin setzen
                    $entityManager->persist($neuerPreisDatensatz); 
                    $entityManager->flush(); // Alle Änderungen in der Datenbank speichern 
        }
    }

//    /**
//     * @return ItemCategoriesPrice[] Returns an array of ItemCategoriesPrice objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ItemCategoriesPrice
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}