<?php

namespace App\Controller;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Objekt;
use App\Entity\RentItems;
use App\Entity\Reservation;
use App\Form\ReservationEditType;
use App\Form\ReservationNewType;
use App\Repository\AreaRepository;
use App\Repository\CompanyRepository;
use App\Repository\ObjektRepository;
use App\Repository\OpeningTimeRepository;
use App\Repository\RentItemsRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\ManagerRegistry as DoctrineManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
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
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }
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
            //check if $companies is a array
            if (!is_array($companies)) {
                //erstelle aus $companies einen array
                $companies = [$companies];
            }
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
                'objekt' => $string
            ]); 
         

    } 
    #[Route('/ajax_freeItamsNow', name: 'ajax_freeItamsNow')]
    public function FreeItamsNow(EntityManagerInterface $entityManager, ObjektRepository $objektRepository, Request $request  ,RentItemsRepository $rentItemsRepository)
    {  
        $objektId= (int)$_POST['id'];
      
        $kommen = new DateTime();
        $objekt = $objektRepository->find($objektId);
        $staytime =  $objekt->getStaytime();
        $gehenTimestamp =  $kommen->getTimestamp() + ($staytime * 60);
        $gehen =   new DateTime(date("Y-m-d H:i:s",$gehenTimestamp));
        $Items = $rentItemsRepository->selectFreeItems($kommen->format('Y-m-d H:i:s'), $gehen->format('Y-m-d H:i:s'), $objektId, 0,1);
           
        return new JsonResponse($Items);
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
        //checken ob ein Points mit der ReservierungsId vorhanden sind
        $atherReservations = $reservationRepository->findBy(['points'=>$res_id]);
            foreach( $atherReservations as  $atherReservation){
                $atherReservation->setStatus('Checked In;'.$now);
                $reservationRepository->save($atherReservation, true);
            }
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
        if(isset($_POST['reservation_edit'])){
        $data = $_POST['reservation_edit'];
        
        // $request->request->get('reservation_edit');
         $res_id =  $data['id'];
    
         $entityManager = $doctrine->getManager();
         $reservation = $entityManager->getRepository(Reservation::class)->find($res_id);
         $kommen = new DateTime($data['kommen']['date'] . ' ' . $data['kommen']['time'] . ':00');
         $objekt = $entityManager->getRepository(RentItems::class)->find($data['item'])->getObjekt();
         $staytime =  $objekt->getStaytime();
             
         //ermitteln gehen
         $gehenTimestamp =  $kommen->getTimestamp() + ($staytime * 60);
         $gehen =   new DateTime(date("Y-m-d H:i:s",$gehenTimestamp));
         //prüfen ob die zeit in den öffnungszeiten liegt
         //prüfen ob kommen geändert wurden
         //prüfen ob Pax geänderte wurde und ob die personen an das item passen
         
         //freies Item finden und prüfen ob eins frei ist!
         $Items = $rentItemsRepository->selectFreeItemsEdit($kommen->format('Y-m-d H:i:s'), $gehen->format('Y-m-d H:i:s'), $objekt->getId(), $data['pax'],1,$res_id);
         if(count($Items)>0){
 
         
             $item = $rentItemsRepository->find($Items[0]['item_id']);
             
             
             $reservation->setKommen($kommen);
             $reservation->setGehen($gehen);
             $reservation->setUser($data['user']);
             $reservation->setPax($data['pax']);
             $reservation->setFon($data['fon']);
             $reservation->setMail($data['mail']);
             $reservation->setItem($item);
         
         
             try {
                 $entityManager->persist($reservation);
                 $entityManager->flush();
             
                 // Überprüfung, ob das Speichern erfolgreich war
                 if ($reservation->getId()) {
                     $res_id = $reservation->getId();
                     $datas = array(
                         'res_id' => $res_id,
                     );
                     $this->addFlash('success', 'Reservierung erfolgreich gespeicher');
                     return new JsonResponse(true);
                 } else {
                     $this->addFlash('danger', 'Fehler beim Speichern der Reservierung');
                 }
             } catch (\Exception $e) {
                 // Fehlerbehandlung, falls das Speichern fehlgeschlagen ist
                 $this->addFlash('danger', 'Fehler beim Speichern der Reservierung: ' . $e->getMessage());
             }
         
             
         }else{
             $data['form'] =[
                'id'=>$res_id,
                 'pax'=>$data['pax'],
                 'user' =>$data['user'],
                 'fon' =>$data['fon'],
                 'mail' =>$data['mail'],
                 'objekt' =>$objekt->getId() ,
                 'Datum' => 
                     ['year'=>'2023','month'=>'05','day'=>'25']
                 
             ];
            }
             return new JsonResponse($data);
         
        }else{
        $data = $_POST['form'];
         

          // $request->request->get('reservation_edit');
         $res_id =  $data['id'];
   
         $entityManager = $doctrine->getManager();
         $reservation = $entityManager->getRepository(Reservation::class)->find($res_id);
         $kommen = new DateTime($data['Uhrzeit']);
         $objekt = $entityManager->getRepository(Objekt::class)->find($data['objekt']);
         $staytime =  $objekt->getStaytime();
             
         //ermitteln gehen
         $gehenTimestamp =  $kommen->getTimestamp() + ($staytime * 60);
         $gehen =   new DateTime(date("Y-m-d H:i:s",$gehenTimestamp));
         //prüfen ob die zeit in den öffnungszeiten liegt
         //prüfen ob kommen geändert wurden
         //prüfen ob Pax geänderte wurde und ob die personen an das item passen
         
         //freies Item finden und prüfen ob eins frei ist!
        
         $Items = $rentItemsRepository->selectFreeItemsEdit($kommen->format('Y-m-d H:i:s'), $gehen->format('Y-m-d H:i:s'), $objekt->getId(), $data['pax'],1,$res_id);
         if(count($Items)>0){
             $item = $rentItemsRepository->find($Items[0]['item_id']);
            $reservation->setKommen($kommen);
             $reservation->setGehen($gehen);
             $reservation->setUser($data['name']);
             $reservation->setPax($data['pax']);
             $reservation->setFon($data['telefon']);
             $reservation->setMail($data['email']);
             $reservation->setItem($item);
             try {
                 $entityManager->persist($reservation);
                 $entityManager->flush();
                 if ($reservation->getId()) {
                     $res_id = $reservation->getId();
                     $this->addFlash('success', 'Reservierung erfolgreich gespeicher');
                     return new JsonResponse(true);
                 } else {
                     $this->addFlash('danger', 'Fehler beim Speichern der Reservierung');
                 }
             } catch (\Exception $e) {
                 // Fehlerbehandlung, falls das Speichern fehlgeschlagen ist
                 $this->addFlash('danger', 'Fehler beim Speichern der Reservierung: ' . $e->getMessage());
             }
        
            }else{
                return new JsonResponse($Items);
            }
        }
       
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
    public function ResResNew(Request $request, ObjektRepository $objektRepository): Response
    {
        $data = $request->request->get('data');
       
        $objekt = $objektRepository->find($data);
        $form = $this->createFormBuilder()
            ->add('objekt', EntityType::class, [
                'class' => Objekt::class,
                'data' => $objekt,
                'label' => false,
                'attr' => [
                    'style' => 'display:none',
                ]
            ])
            ->add('Datum', DateType::class, [
                'data' => new \DateTime(),
                'label' => 'Datum',
            ])
            ->add('pax', NumberType::class, [
                'label' => 'Anzahl Personen'
            ])
            ->add('next', SubmitType::class, [
                'label' => 'Verfügbarkeit prüfen',
                
                'attr' => [
                    'data-objekt' => $data,
                ],
            ])
            ->getForm();

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
    #[Route('/ajax_Group_select_item', name: 'ajax_Group_select_item')]
    public function GroupSelectItem(EntityManagerInterface $entityManager, OpeningTimeRepository $openingTimeRepository, RentItemsRepository $rentItemsRepository, ObjektRepository $objektRepository,UserRepository $userRepository,AuthenticationUtils $authenticationUtils, Request $request,ReservationRepository $reservationRepository): Response 
    {   //speichern der Reservierungen
        if(isset($_POST['form']['name'])){
            $data = $_POST['form'];
            $objektId = $data['objekt'];
            $objekt = $objektRepository->find($objektId);
            $UhrzeitStr = $data['Uhrzeit'];
            $Uhrzeit = new DateTime($UhrzeitStr);
            $pax = $data['pax'];
            $Items_id = $data['Item'];
            $name = $data['name'];
            $email = $data['email'];
            $telefon = $data['telefon']; 
            $staytime =  $objekt->getStaytime();
            $count = 0;
            $Res_id = null;
            foreach($Items_id  as $Item_id){
                $item = $rentItemsRepository->find($Item_id);
                $reservation = new Reservation();
                $reservation->setMail($email);
                $reservation->setUser($name);
                $reservation->setFon($telefon);
                $reservation->setPax($pax);
                $reservation->setKommen($Uhrzeit);
                $kommen = $reservation->getKommen();
                $reservation->setGehen(new DateTime(date("Y-m-d H:i",$kommen->getTimestamp() + ($staytime * 60))));
                $reservation->setItem($item);
                if($count > 0){
                    $reservation->setPoints($Res_id);   
                }
                $entityManager->persist($reservation);
                $entityManager->flush();
                if($count < 1){
                    $Res_id = $reservation->getId();
                }
                $count++;
                
            }
           // dump($reservation);
            return new JsonResponse('success');
        }
        //Reservierungsformular und Item auswahl anzeigen
        else{
            $data = $_POST['form'];
            $datum = $data['Uhrzeit'];
            $objekt = $data['objekt'];
            $objektData = $objektRepository->find($objekt);
            $pax = $data['pax'];
            $items = $rentItemsRepository->freeItems($datum, $objekt, 0, 1, 0);
            foreach($items as $item){
                
                $choices[$item['name'].' Pax: '.$item['pax']] = $item['item_id'];
            }
            $form = $this->createFormBuilder()
            ->add('objekt', EntityType::class, [
                'class' => Objekt::class,
                'data' => $objektData,
                'label' => false,
                'attr' => [
                    'style' => 'display:none',
                ]
            ])
            ->add('Uhrzeit', TextType::class, [
                'data' => $datum,
                'label' => false,
                'attr' => [
                    'style' => 'display:none',
                ]
            
            ])
            ->add('pax', NumberType::class, [
                'data' => $pax,
                'label' => false,
                'attr' => [
                    'style' => 'display:none',
                ]
            ])  
            ->add('Item', ChoiceType::class, [
                'choices' => $choices,
                'expanded' => true,
                'multiple' => true,
                'label' => 'Bitte Pätze auswählen',
                'attr'=>['class'=>'Itam_select'],
            ])
            ->add('name', TextType::class, [
                'label' => 'Name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-Mail',
            ])
            ->add('telefon', TextType::class, [
                'label' => 'Telefon',
            ])
                
        
            ->add('Save', SubmitType::class,[
                'attr'=>['class'=>'w-100 btn-info btn '],
            ])
            ->getForm();
            return $this->renderForm('reservations/availabilityGroup.html.twig', [
                'form'=> $form,
                'data' => $data,
                
            ]);
        }
          
    }
    #[Route('/ajax_availability', name: 'ajax_availability')]
    public function availability(OpeningTimeRepository $openingTimeRepository, RentItemsRepository $rentItemsRepository, ObjektRepository $objektRepository,UserRepository $userRepository,AuthenticationUtils $authenticationUtils, Request $request,ReservationRepository $reservationRepository): Response 
    {

        $data = $_POST['form'];
        $datum = $data['Datum'];
        $objekt = $data['objekt'];
        $pax = $data['pax'];
        $objektData = $objektRepository->find($objekt);
        $day = new DateTime();
        $day->setDate($datum['year'],$datum['month'],$datum['day']);
        $wochentag = $day->format('w');
        $openingtimes = $openingTimeRepository->findBy(['objekt' => $objekt, 'day' => $wochentag]);
        $interval = 15 * 60; // 15 Minuten in Sekunden
       // schleife öffnungszeiten für auswahl openng times
        foreach ($openingtimes as $openingtime) {
            $start = $openingtime->getStart()->getTimestamp();
            $ende = $openingtime->getEnd()->getTimestamp();
    
            for ($time = $start; $time <= $ende; $time += $interval) {
                $datetime = new DateTime();
               
                $datetime->setDate($datum['year'],$datum['month'],$datum['day']);
                 // Das gewünschte Datum setzen
                $datetime->setTime(date('H', $time), date('i', $time));
                $zeit = $datetime->format('Y-m-d H:i:s');
                $choicesTime = $datetime->format('H:i');
                $items = $rentItemsRepository->freeItems($zeit, $objekt, $pax, 1, 0);
                
                if (count($items) > 0) {
                    $choices[$choicesTime] = $zeit;
                }
               
            }
        }

        if(!isset($choices)){
            foreach ($openingtimes as $openingtime) {
                $start = $openingtime->getStart()->getTimestamp();
                $ende = $openingtime->getEnd()->getTimestamp();
        
                for ($time = $start; $time <= $ende; $time += $interval) {
                    $datetime = new DateTime();
                   
                    $datetime->setDate($datum['year'],$datum['month'],$datum['day']);
                     // Das gewünschte Datum setzen
                    $datetime->setTime(date('H', $time), date('i', $time));
                    $zeit = $datetime->format('Y-m-d H:i:s');
                    $choicesTime = $datetime->format('H:i');
                    $items = $rentItemsRepository->freeItems($zeit, $objekt, 0, 1, 0);
                    
                    if (count($items) > 0) {
                        $choices[$choicesTime] = $zeit;
                    }
    
                }
            }
           
            $form = $this->createFormBuilder()
            ->add('objekt', EntityType::class, [
                'class' => Objekt::class,
                'data' => $objektData,
                'label' => false,
                'attr' => [
                    'style' => 'display:none',
                ]
            ])
            ->add('pax', NumberType::class, [
                'data' => $pax,
                'label' => false,
                'attr' => [
                    'style' => 'display:none',
                ]
            ])
         
            ->add('Uhrzeit', ChoiceType::class, [
                'choices' => $choices,
                'expanded' => true,
                'multiple' => false,
                'label' => 'Uhrzeit',
            
            ])
            ->add('los', SubmitType::class,[
                'label' => 'reservieren',
                'attr' => [
                    'class' => 'w-100',
                ]
            ])
            ->getForm();

            return $this->renderForm('reservations/availabilityGroup.html.twig', [
                'form'=> $form,
                'data' => $data,
                
            ]);      
        }else{
        
          
        
        //neue Reserverung
        
            $form = $this->createFormBuilder()
         
                ->add('objekt', EntityType::class, [
                    'class' => Objekt::class,
                    'data' => $objektData,
                    'label' => false,
                    'attr' => [
                        'style' => 'display:none',
                    ]
                ])
                ->add('Uhrzeit', ChoiceType::class, [
                    'choices' => $choices,
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Uhrzeit',
                
                ])
                ->add('pax', NumberType::class, [
                    'data' => $pax,
                    'label' => false,
                    'attr' => [
                        'style' => 'display:none',
                    ]
                ])
                ->add('name', TextType::class, [
                    'label' => 'Name',
                ])
                ->add('email', EmailType::class, [
                    'label' => 'E-Mail',
                ])
                ->add('telefon', TextType::class, [
                    'label' => 'Telefon',
                ])
                ->add('los', SubmitType::class)
            ->getForm();
        
         //   $form->handleRequest($request);

            return $this->renderForm('reservations/availability.html.twig', [
               'form' => $form,
           ]);
        }
    }
    #[Route('/ajax_availability_edit', name: 'ajax_availability_edit')]
    public function availabilityEdit(OpeningTimeRepository $openingTimeRepository, RentItemsRepository $rentItemsRepository, ObjektRepository $objektRepository,UserRepository $userRepository,AuthenticationUtils $authenticationUtils, Request $request,ReservationRepository $reservationRepository): Response 
    { 
        $data = $_POST['form'];
        //Prüfen ob Kommen variable Geändert wurde
        if(isset( $_POST['kommen'])){
           // dump($_POST['kommen']['date']);
            $kommen = $_POST['kommen']['date'];
            $kommenTime = $_POST['kommen']['time'];
            $datum = ['year'=>date('Y',strtotime($kommen)),'month'=>date('m',strtotime($kommen)),'day'=>date('d',strtotime($kommen))];
        }else{
            $datum = $data['Datum'];
        }
        
        $objekt = $data['objekt'];
        $pax = $data['pax'];
        $objektData = $objektRepository->find($objekt);
        $day = new DateTime();
        $day->setDate($datum['year'],$datum['month'],$datum['day']);
        $wochentag = $day->format('w');
        $openingtimes = $openingTimeRepository->findBy(['objekt' => $objekt, 'day' => $wochentag]);
        $interval = 15 * 60; // 15 Minuten in Sekunden
       // schleife öffnungszeiten für auswahl openng times
        foreach ($openingtimes as $openingtime) {
            $start = $openingtime->getStart()->getTimestamp();
            $ende = $openingtime->getEnd()->getTimestamp();
    
            for ($time = $start; $time <= $ende; $time += $interval) {
                $datetime = new DateTime();
               
                $datetime->setDate($datum['year'],$datum['month'],$datum['day']);
                 // Das gewünschte Datum setzen
                $datetime->setTime(date('H', $time), date('i', $time));
                $zeit = $datetime->format('Y-m-d H:i:s');
                $choicesTime = $datetime->format('H:i');
                if(isset( $_POST['id'])){ 
                    $res_id =  $_POST['id'];
                    $temp_datum = $kommen.' '.$kommenTime;
                    //dump($temp_datum);
                     $items = $rentItemsRepository->freeItems($temp_datum, $objekt, $pax, 1,$res_id);
                }
                else{
                        $items = $rentItemsRepository->freeItems($zeit, $objekt, $pax, 1, 0);
                }
                if (count($items) > 0) {
                    $choices[$choicesTime] = $zeit;
                }

            }
        }

        if(!isset($choices)){
            
           //Res bearbeiten
           if(isset( $_POST['id'])){ 
           $res_id =  $_POST['id'];
           $temp_datum = $kommen.' '.$kommenTime;
           //dump($temp_datum);
            $items = $rentItemsRepository->freeItems($temp_datum, $objekt, 0, 1,$res_id);
           }
           
          // dump($items);
            foreach($items as $item){
                $itamId= $item['item_id'];
                $itamPax= $item['pax'];
                $itaName= $item['name'];

                $choices[$itaName." Pax: ".$itamPax] = $itamId;
           }
           dump($data);
           $form = $this->createFormBuilder()
         
              
                ->add('Item', ChoiceType::class, [
                    'choices' => $choices,
                    'expanded' => true,
                    'multiple' => true,
                    'label' => 'Item',
                ])
                ->add('los', SubmitType::class)
                ->getForm();
               
            //ende test
            return $this->renderForm('reservations/availabilityGroup.html.twig', [
                'form'=> $form,
                'data' => $data,
                
            ]);      
        }else{
        //Reservierung Bearbeiten
        if(isset($_POST['id'])){
         
            $form = $this->createFormBuilder()
            ->add('id',TextType::class,[
                'label' => false,
                'data' => $_POST['id'],
                'attr' => [
                    'style' => 'display:none',
                ]
            ])
            ->add('objekt', EntityType::class, [
                'class' => Objekt::class,
                'data' => $objektData,
                'label' => false,
                'attr' => [
                    'style' => 'display:none',
                ]
            ])
            ->add('Uhrzeit', ChoiceType::class, [
                'choices' => $choices,
                'expanded' => true,
                'multiple' => false,
                'label' => 'Uhrzeit',
               
            ])
            ->add('pax', NumberType::class, [
                'data' => $pax,
                'label' => false,
                'attr' => [
                    'style' => 'display:none',
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Name',
                'data' => $_POST['user'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-Mail',
                'data' => $_POST['mail'],
            ])
            ->add('telefon', TextType::class, [
                'label' => 'Telefon',
                'data' => $_POST['fon'],
            ])
            ->add('Save', SubmitType::class)
         ->getForm();
          
        }
        //neue Reserverung
        else{
            $form = $this->createFormBuilder()
         
                ->add('objekt', EntityType::class, [
                    'class' => Objekt::class,
                    'data' => $objektData,
                    'label' => false,
                    'attr' => [
                        'style' => 'display:none',
                    ]
                ])
                ->add('Uhrzeit', ChoiceType::class, [
                    'choices' => $choices,
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Uhrzeit',
                
                ])
                ->add('pax', NumberType::class, [
                    'data' => $pax,
                    'label' => false,
                    'attr' => [
                        'style' => 'display:none',
                    ]
                ])
                ->add('name', TextType::class, [
                    'label' => 'Name',
                ])
                ->add('email', EmailType::class, [
                    'label' => 'E-Mail',
                ])
                ->add('telefon', TextType::class, [
                    'label' => 'Telefon',
                ])
                ->add('los', SubmitType::class)
            ->getForm();
        }
         //   $form->handleRequest($request);

            return $this->renderForm('reservations/availability.html.twig', [
               'form' => $form,
           ]);
        }
    }
    
    #[Route('/ajax_ResNewSave', name: 'ajax_ResNewSave')]
    public function ResNewSave(EntityManagerInterface $entityManager, RentItemsRepository $rentItemsRepository ,ObjektRepository $objektRepository, UserRepository $userRepository, AuthenticationUtils $authenticationUtils, Request $request, ReservationRepository $reservationRepository):Response
    {   
        
        
        if ($authenticationUtils && isset($_POST['form'])) {
            $lastUsername = $authenticationUtils->getLastUsername();
            $data = $_POST['form'];
          
            $reservation = new Reservation();
            $username = isset($data['name']) ? (string) $data['name'] : null;
            $reservation->setUser($username);
            $reservation->setUser($data['name']);
            $reservation->setPax($data['pax']);
            $reservation->setFon($data['telefon']);
            $reservation->setMail($data['email']);
            $objekt_id = $data['objekt'];
            $objekt = $objektRepository->find($objekt_id);
            $staytime =  $objekt->getStaytime();
            $reservation->setKommen(DateTime::createFromFormat('Y-m-d H:i:s', $data['Uhrzeit']));
            $kommen = $reservation->getKommen();
            $reservation->setGehen(new DateTime(date("Y-m-d H:i",$kommen->getTimestamp() + ($staytime * 60))));
            $gehen = $reservation->getGehen();
            $Items = $rentItemsRepository->selectFreeItems($kommen->format('Y-m-d H:i:s'), $gehen->format('Y-m-d H:i:s'), $objekt_id, $data['pax'],1);
            $item = $rentItemsRepository->find($Items[0]['item_id']);
            $reservation->setItem($item); // Das erste Element der Liste zuweisen
           
            try {
                $entityManager->persist($reservation);
                $entityManager->flush();
            
                // Überprüfung, ob das Speichern erfolgreich war
                if ($reservation->getId()) {
                    $res_id = $reservation->getId();
                    $datas = array(
                        'res_id' => $res_id,
                    );
                    $this->addFlash('success', 'Reservierung erfolgreich gespeicher');
                    return new JsonResponse($datas);
                } else {
                    $this->addFlash('danger', 'Fehler beim Speichern der Reservierung');
                }
            } catch (\Exception $e) {
                // Fehlerbehandlung, falls das Speichern fehlgeschlagen ist
                $this->addFlash('danger', 'Fehler beim Speichern der Reservierung: ' . $e->getMessage());
            }
       
       
       
       
          
        }
       
    }
}


