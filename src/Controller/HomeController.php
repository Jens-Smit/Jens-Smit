<?php

namespace App\Controller;

use App\Repository\ObjektCategoriesRepository;
use App\Repository\ObjektRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
   public function index(ObjektRepository $objektRepository, ObjektCategoriesRepository $objektCategoriesRepository): Response
    {
        $objekts = $objektRepository->findAll();
        $categories = $objektCategoriesRepository->findAll();
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'objekts'=> $objekts,
            'categories'=> $categories,
            'info' => 'Übersicht der Objekte'
        ]);
    }
   /* public function index(Request $request,RentItemsRepository $rentItemsRepository ,ReservationRepository $reservationRepository): Response
    {
        $items = $reservationRepository->findAll();
        $Avform = $this->createFormBuilder()
        ->add('date', DateTimeType::class, [
            'date_label' => 'Datum',
            ])
        ->add('pax', NumberType::class)
        ->add('send', SubmitType::class)
        ->getForm()
        ;
        $Avform->handleRequest($request);

        if ($Avform->isSubmitted()){
            
        $id = 1;
        $eingabe = $Avform->getData();
        $date = $eingabe['date'];
        $datestr = $date->format( 'Y-m-d H:i:s' ); 
        $time_edd= strtotime($datestr)+(60*60);
        $time  = new DateTime();
        $time->setTimestamp($time_edd);
        $timestr = $time->format( 'Y-m-d H:i:s' );
        




        $pax = $eingabe['pax'];
        
        $items = $rentItemsRepository->findfree( $datestr,$timestr, $id, $pax);
           
        $info = 'test';   
        return $this->render('home/index.html.twig', [
            'info' => $info,
            'Avform' => $Avform->createView(),
            'items' => $items,
        ]);

        }
        return $this->render('home/index.html.twig', [
            'info' => 'Alle reservierungen',
            'Avform' => $Avform->createView(),
            'items' => $items,
        ]);
    }
    */
}

    