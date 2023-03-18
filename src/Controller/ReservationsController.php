<?php

namespace App\Controller;

use App\Entity\Objekt;
use App\Entity\RentItems;
use App\Entity\Reservation;
use App\Form\ReservationEditType;
use App\Form\ReservationNewType;
use App\Repository\AreaRepository;
use App\Repository\CompanyRepository;
use App\Repository\ObjektRepository;
use App\Repository\RentItemsRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\ManagerRegistry as DoctrineManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

$encoders = [new XmlEncoder(), new JsonEncoder()];
$normalizers = [new ObjectNormalizer()];

$serializer = new Serializer($normalizers, $encoders);
class ReservationsController extends AbstractController
{
    #[Route('/res/{id}', name: 'app_reservations')]
    public function index(Objekt $string,Request $request, CompanyRepository $companyRepository , ObjektRepository $objektRepository,AreaRepository $areaRepository ,ReservationRepository $reservationRepository, ): Response
    {   

        // Aktuellen Benutzer abrufen
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        
        // Unternehmen für Benutzer abrufen - Admin eines Unternehmens
        $companies = $companyRepository->findBy(['onjekt_admin' => $user]);
        
        // Wenn der Benutzer kein Admin eines Unternehmens ist, verwende das eigene Unternehmen
        if (empty($companies)) {
            $companies  = $this->container->get('security.token_storage')->getToken()->getUser()->getCompany();
            
        }
        
        if (!empty($companies)) {
            // Alle Objekte für die Unternehmen mit einer einzigen Abfrage mittels IN-Operator abrufen
            $companyIds = array_map(function($company) { return $company->getId(); }, $companies);
            $objekts = $objektRepository->findBy(['company' => $companyIds]);
            $contol = false;
            foreach($objekts as $objekt){
               if ($objekt == $string) {
                $contol = true;
               }
            }
           
        }
        if($contol == false){
            return $this->render('dashboard/noroles.html.twig', [
                
            ]); 
        }
        $objekt  = $string;
        $areas = $areaRepository->findBy(['objekt'=> $objekt]);
       $reservations = $reservationRepository->findByObjekt_Date_Time( $objekt->getId() );
           // Formular erstellen
        $form = $this->createFormBuilder()
        ->add('Reservation', TextType::class, [
            'label' => 'Reservation'  ,
            'attr'=>[
                'class' => 'Reservation',
                ]])
        ->add('Tisch', TextType::class, [
            'label' => 'Tisch' ,
            'attr'=>[
                'class' => 'Tisch',
                ]])
        ->add('send', SubmitType::class,[
            'label' => 'los',
            'attr'=>[
                'class' => 'btn-info btn',
                'style' =>  'width:100%;',]
        ])
        ->getForm()
        ;
        $form->handleRequest($request);
    
            return $this->render('reservations/index.html.twig', [
                'form' => $form->createView(),
                'Reservations' => $reservations,
                'Areas' => $areas,
            ]); 
         

    } 
    #[Route('/ajax_ResToTable', name: 'ajax_ResToTable')]
    public function ResToTable(ManagerRegistry $doctrine, Request $request  ,ReservationRepository $reservationRepository)
    {
        if($request->get('tab_id') == "reservierungen"){
            $res_id = substr($request->get('res_id'),3); 
            $entityManager = $doctrine->getManager();
            $reservation = $entityManager->getRepository(Reservation::class)->find($res_id);
            $reservation->setStatus(null);
            $reservationRepository->save($reservation, true);
        }else{
        $res_id = substr($request->get('res_id'),3);
        $tab_id = substr($request->get('tab_id'),3);
        $entityManager = $doctrine->getManager();
        $reservation = $entityManager->getRepository(Reservation::class)->find($res_id);
        $item = $entityManager->getRepository(RentItems::class)->find($tab_id); 
        $now = date("Y-m-d H:i",time());
        $reservation->setStatus('Checked In;'.$now);
        $reservation->setItem( $item);
        $reservationRepository->save($reservation, true);
        }


        
       
        $datas = array(
            'res_id' => $res_id
        );
        return new JsonResponse($datas);
        

    }
    
    #[Route('/ajax_ResToTable_WalkIn', name: 'ajax_ResToTable_WalkIn')]
    public function ResToTable_WalkIn( ObjektRepository $objektRepository, Request $request, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository, RentItemsRepository $rentItemsRepository)
    {
        $pax = $request->get('pax');
        $tab_id = substr($request->get('tab_id'),3);
        $item = $rentItemsRepository->find($tab_id);  
        $objekt = $item->getObjekt();
        $objekt_id = $objekt->getId();
        $kommen =  new DateTime(date("Y-m-d H:i",time()));
        $staytime = $objektRepository->find($objekt_id)->getStaytime();
        $reservation = new Reservation();
        $reservation->setUser("Walkin");
        $reservation->setMail('WalkIn@xx.xx');
        $reservation->setFon('000');
        $reservation->setPoints('1');
        $reservation->setStatus('Checked In;'.date("Y-m-d H:i",time()));
        $reservation->setItem($item);
        $reservation->setKommen( $kommen);
        $reservation->setGehen(new DateTime(date("Y-m-d H:i",$kommen->getTimestamp() + ($staytime * 60))));
        $reservation->setPax($pax);

        $entityManager->persist($reservation);
        $entityManager->flush();
        $res_id = $reservation->getId();

        $datas = array(
            'res_id' => $res_id,
        );
        return new JsonResponse($datas);
    }
    #[Route('/ajax_ResEdit', name: 'ajax_ResEdit')]
    public function ResEdit(ManagerRegistry $doctrine, Request $request,  ReservationRepository $reservationRepository)
    { 
        $res_id = substr($request->get('id'),3);
        $entityManager = $doctrine->getManager();
        $reservation = $entityManager->getRepository(Reservation::class)->find($res_id);

        $form = $this->createForm(ReservationEditType::class,$reservation );
        $form->handleRequest($request);

       

        return $this->renderForm('reservations/editForm.html.twig', [
            
            'form' => $form,
        ]);



        

    }
    #[Route('/ResCheckIn', name: 'ResCheckIn')]
    public function ResCheckIn(ManagerRegistry $doctrine,Request $request, ReservationRepository $reservationRepository){
        $res_id = substr($request->get('id'),3);
        $entityManager = $doctrine->getManager();
        $reservation = $entityManager->getRepository(Reservation::class)->find($res_id);
        $reservationRepository->save($reservation, true);
    }
    #[Route('/ajax_ResEditUpdate', name: 'ajax_ResEditUpdate', methods: ['GET', 'POST'])]
    public function ResEditUpdate(ManagerRegistry $doctrine,  RentItemsRepository $rentItemsRepository, Request $request)
    { 
        $data = $_POST['reservation_edit'];
       // $request->request->get('reservation_edit');
        $res_id =  $data['id'];
   
        $entityManager = $doctrine->getManager();
        $reservation = $entityManager->getRepository(Reservation::class)->find($res_id);
    
        $item = $rentItemsRepository->findOneBy(['id' =>  $data['item']]);
        $gehen = new DateTime($data['gehen']['date'] . ' ' . $data['gehen']['time'] . ':00');
        $kommen = new DateTime($data['kommen']['date'] . ' ' . $data['kommen']['time'] . ':00');
        
        
        $reservation->setKommen($kommen);
        $reservation->setGehen($gehen);
        $reservation->setUser($data['user']);
        $reservation->setPax($data['pax']);
        $reservation->setFon($data['fon']);
        $reservation->setMail($data['mail']);
        $reservation->setItem($item);
        $reservation->setPoints($data['points']);
       
        
    
        $entityManager->persist($reservation);
        $entityManager->flush();
        
        return $this->redirectToRoute('app_reservations');
        
    }
    
    #[Route('/ajax_ResCheckOut', name: 'ajax_ResCheckOut')]
    public function ResCheckOut(Request $request, ManagerRegistry $doctrine,  ReservationRepository $reservationRepository)
    {
        $res_id = substr($request->get('id'),3);
        
        $entityManager = $doctrine->getManager();
        $reservation = $entityManager->getRepository(Reservation::class)->find($res_id);
        $now = date("Y-m-d H:i",time());
        $reservation->setStatus('Checked Out;'.$now);
        $reservation->setAktiv('Checked Out;'.$now);
        $reservationRepository->save($reservation, true);
      $datas = array(
        'res_id' => $res_id
     );
     return new JsonResponse($datas);

 
    }
    #[Route('/ajax_ResNew', name: 'ajax_ResNew')]
    public function ResResNew(Request $request):Response
    {
       
        $reservation = new Reservation();
        $form = $this->createForm(ReservationNewType::class, $reservation);
        $form->handleRequest($request);
               
        return $this->renderForm('reservations/newForm.html.twig', [
            'form' => $form,
            
        ]);
    }
    #[Route('/ajax_onPreSetData', name: 'onPreSetData')]
    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $form->add('points',NumberType::class);
    }
    #[Route('/ajax_ResNewSave', name: 'ajax_ResNewSave')]
    public function ResNewSave(RentItemsRepository $rentItemsRepository ,ObjektRepository $objektRepository, UserRepository $userRepository, AuthenticationUtils $authenticationUtils, Request $request, ReservationRepository $reservationRepository):Response
    {  if ($authenticationUtils && isset($_POST['reservation_new'])) {
        $lastUsername = $authenticationUtils->getLastUsername();
        $data = $_POST['reservation_new'];
        $form = $this->createForm(ReservationNewType::class);
        $form->handleRequest($request);
        $reservation = new Reservation();
        $reservation->setUser($data['user']);
        $reservation->setPax($data['pax']);
        $reservation->setFon($data['fon']);
        $reservation->setMail($data['mail']);
        $reservation->setPoints($data['points']);
        $reservation->setKommen(DateTime::createFromFormat('Y-m-d H:i:s', $data['kommen']['date'] . ' ' . $data['kommen']['time'] . ':00'));
        $kommen = $reservation->getKommen();
        
        $objekt_id = $userRepository->findOneBy(['email' => $lastUsername])->getObjekt()->getId();
        
        $staytime = $objektRepository->findOneBy(['id' => $objekt_id])->getStaytime();
         $pax = $data['pax'];
        $reservation->setGehen(new DateTime('@' . (strtotime($kommen->format('Y-m-d H:i:s')) + ($staytime * 60))));
        $reservation->setPax($pax);
       
        
       
        /*
       
        
        $reservation->setItem($data['item']);*/
        $reservation_id = $reservationRepository->save($reservation);
        if ($reservation_id) {
            $this->addFlash('success', 'Reservierung erfolgreich gespeichert!');
        } else {
            $this->addFlash('danger', 'Reservierung konnte nicht gespeichert werden!');
        }
        return $this->redirectToRoute('app_reservations');
    }
    }
}


