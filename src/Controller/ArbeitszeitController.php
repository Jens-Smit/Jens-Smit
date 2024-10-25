<?php

namespace App\Controller;

use App\Entity\Arbeitszeit;
use App\Form\ArbeitszeitType;
use App\Repository\ArbeitszeitRepository;
use App\Repository\ContractDataRepository;
use App\Repository\DiensteRepository;
use App\Repository\FehlzeitenRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Time;

#[Route('/arbeitszeit')]        
class ArbeitszeitController extends AbstractController
{
    #[Route('/', name: 'app_arbeitszeit', methods: ['GET'])]
    public function index(ArbeitszeitRepository $arbeitszeitRepository): Response
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $Arbeitszeit = $arbeitszeitRepository->findBy( ['user' => $user, 'Austrittszeit' => null ]);
      
        return $this->render('arbeitszeit/index.html.twig', [
            'user' => $user ,
            'Arbeitszeiten' => $Arbeitszeit ,

        ]);
    }
    #[Route('/checkIn', name: 'app_arbeitszeit_checkIn', methods: ['GET'])]
    public function checkIn(ManagerRegistry $doctrine): Response
    {
        
        $time = new DateTime(date("H:i:s"));
        $day = new DateTime(date("Y-m-d"));
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $Arbeitszeit = new Arbeitszeit();
        $Arbeitszeit->setUser($user);
        $Arbeitszeit->setEintrittszeit($time);
        $Arbeitszeit->setDatum($day);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($Arbeitszeit);
        $entityManager->flush();
        if ($entityManager->contains($Arbeitszeit)) {
            $datas = true;
        } else {
            $datas = false;
        }
        return new JsonResponse($datas);
    }
    #[Route('/checkOut', name: 'app_arbeitszeit_checkOut', methods: ['GET'])]
    public function checkOut(ArbeitszeitRepository $arbeitszeitRepository,ManagerRegistry $doctrine): Response
    {
        
        $time = new DateTime(date("H:i:s"));
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $Arbeitszeiten = $arbeitszeitRepository->findBy( ['user' => $user, 'Austrittszeit' => null ]);
        $Arbeitszeit = $Arbeitszeiten[0];
        $Arbeitszeit->setUser($user);
        $Arbeitszeit->setAustrittszeit($time);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($Arbeitszeit);
        $entityManager->flush();
        if ($entityManager->contains($Arbeitszeit)) {
            $datas = true;
        } else {
            $datas = false;
        }
        return new JsonResponse($datas);
    }
    #[Route('/amendment', name: 'app_arbeitszeit_amendment', methods: ['GET', 'POST'])]
    public function amendment(Request $request): Response
    {
        $data = $request->request->all();
        
        $kommen = $data['kommen'];
        $gehen = $data['gehen'];
        $day = $data['day'];
        $userId = $data['userId'];

        $jsonData = json_encode($data);

        $publicDirectory = $this->getParameter('kernel.project_dir') . '/public';
        $filename = sprintf('%s/data/dienstplan/aenerungsantrag_%s_%s.json', $publicDirectory, $day, $userId);

        file_put_contents($filename, $jsonData);
       
            $datas = true; 
         
        return new Response($datas);
    }
    #[Route('/ckeckin_api', name: 'app_arbeitszeit_ckeckin_api', methods: ['GET', 'POST'])]
    public function ckeckin_api(UserRepository $userRepository,ManagerRegistry $doctrine, ArbeitszeitRepository $arbeitszeitRepository): Response
    {   
        // API-Schlüssel
        $apiKey = 'geheimerKey';
        if (!isset($_SERVER['HTTP_ACCESSTOKEN']) || $_SERVER['HTTP_ACCESSTOKEN'] !== $apiKey) {
            header('HTTP/1.1 401 Unauthorized');
            die('Ungültiger API-Schlüssel');
        }
        $data = $_POST['data'];
        $data = json_decode($data);
        $time = new DateTime(date("H:i:s"));
        $user = $userRepository->find($data);
        $entityManager = $doctrine->getManager();
      
        if($user == null){
            $datas = ["status"=> false, 'message'=>'user ist nicht definiert'];
        }
        else{
            $Arbeitszeiten = $arbeitszeitRepository->findBy( ['user' => $user, 'Austrittszeit' => null ]);
            if(count($Arbeitszeiten)>1){
                $datas = ["status"=> false, 'message'=>'User ist mehrere mal eingcheckt'];
            }
            elseif(count($Arbeitszeiten)>0){
                $Arbeitszeiten = $arbeitszeitRepository->findBy( ['user' => $user, 'Austrittszeit' => null ]);
                $Arbeitszeit = $Arbeitszeiten[0];
                $Arbeitszeit->setUser($user);
                $Arbeitszeit->setAustrittszeit($time);
                $entityManager->persist($Arbeitszeit);
            }
            else{
                //Checkin
                $day = new DateTime(date("Y-m-d"));
                $Arbeitszeit = new Arbeitszeit();
                $Arbeitszeit->setUser($user);
                $Arbeitszeit->setEintrittszeit($time);
                $Arbeitszeit->setDatum($day);
                $entityManager->persist($Arbeitszeit);
            }
            //save und Check
            $entityManager->flush();
            if ($entityManager->contains($Arbeitszeit)) {
                $datas = $datas = ["status"=> true, 'message'=>'erfolgreich gespeichert'];
            } else {
                $datas = ["status"=> false, 'message'=>'fehler beim speichern'];
            }
        }
        return new JsonResponse($datas); 
    }
    #[Route('/opanamendment', name: 'app_arbeitszeit_opanamendment', methods: ['GET', 'POST'])]
    public function opanamendment(UserRepository $userRepository): Response
    {
        $publicDirectory = $this->getParameter('kernel.project_dir') . '/public';
        $path = sprintf('%s/data/dienstplan/', $publicDirectory);
        $files = glob($path . 'aenerungsantrag_*.json');
        $data = [];
        $count = 0;
        foreach ($files as $file) {
            $data[] = json_decode(file_get_contents($file), true);   
            $day = $data[$count]['day'];
            $userId = $data[$count]['userId'];  
            $user = $userRepository->find($userId);
            //dienste
            $dienste = $user->getDienste()->getValues();
            $array =[] ;
            foreach($dienste as $dienst){
                $tag = $dienst->getKommen()->format('Y-m-d');
                $kommen = $dienst->getKommen()->format('H:i');
                $gehen = $dienst->getGehen()->format('H:i');
                $dienstId = $dienst->getId();
                if($tag == $day){
                    $array[] = [
                        'kommen' => $kommen,
                        'gehen' => $gehen,
                        'dienstId' => $dienstId,
                    ];
                } 
            } 
            $data[$count]['dienst'] = $array;
            //Arbeitzzeiten
         
            $arbeitszeiten = $user->getArbeitszeits()->getValues();
            $array =[] ;
            foreach($arbeitszeiten as $arbeitszeit){
                
                $kommen = $arbeitszeit->getEintrittszeit()->format('H:i');
                if ($arbeitszeit->getAustrittszeit() !== null) {
                    $gehen = $arbeitszeit->getAustrittszeit()->format('H:i');
                } else {
                    $gehen = $arbeitszeit->getAustrittszeit();
                }
               
                $arbeitszeitId = $arbeitszeit->getId();
                $datum = $arbeitszeit->getDatum()->format('Y-m-d');
                if($arbeitszeit->getDatum()->format('Y-m-d') == $day){
                    $array[] = [
                        'datum' => $datum,
                        'kommen' => $kommen,
                        'gehen' => $gehen,
                        'arbeitszeitId' => $arbeitszeitId,
                    ];
                } 
            }
            $data[$count]['arbeitszeit'] = $array;
            $count ++;

        }
        $Company = $this->container->get('security.token_storage')->getToken()->getUser()->getCompany();
        
        $rool =$this->container->get('security.token_storage')->getToken()->getUser()->getRoles();
        if(in_array('ROLE_HR',$rool)){
            $users = $userRepository->findBy(['company' => $Company]);
            return $this->render('arbeitszeit/amendment.html.twig', [
                        'data' => $data ,
                        'users' => $users ,
            ]);    
        
        }else{
            return new JsonResponse('noroles');
        }
       
    }
    #[Route('/SaveAmendment', name: 'app_arbeitszeit_SaveAmendment', methods: ['GET', 'POST'])]
    public function SaveAmendment(Request $request, ManagerRegistry $doctrine,ArbeitszeitRepository $arbeitszeitRepository): Response
    {
        $data = $request->request;
        $id = $data->get('id');
        $kommen = new \DateTime($data->get('kommen'));
        $gehen = new \DateTime($data->get('gehen'));
        $Arbeitszzeit= $arbeitszeitRepository->find($id);
        $Arbeitszzeit->setEintrittszeit($kommen);
        $Arbeitszzeit->setAustrittszeit($gehen);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($Arbeitszzeit);
        $entityManager->flush();
        $day  = $kommen->format('Y-m-d');
        $userId =  $Arbeitszzeit->getUser()->getId();
        if ($entityManager->contains($Arbeitszzeit)) {
            
            $publicDirectory = $this->getParameter('kernel.project_dir') . '/public';
            $filename = sprintf('%s/data/dienstplan/aenerungsantrag_%s_%s.json', $publicDirectory, $day, $userId);
            if (unlink($filename)) {$datas = true; } 
            else {$datas = false;  }
        } 
        else {
            $datas = false; 
        } 
        dump($datas);
        return new Response($datas);
    }
    #[Route('/edit', name: 'app_arbeitszeit_edit', methods: ['GET', 'POST'])]
    public function edit(UserRepository $userRepository, ManagerRegistry $doctrine, ArbeitszeitRepository $arbeitszeitRepository, Request $request): Response
    {
        
        $users = $userRepository->findAll();
        $arbeitszeiten = $arbeitszeitRepository->findAll();
        $forms = [];    
        foreach ($arbeitszeiten as $arbeitszeit) {
            $form = $this->createForm(ArbeitszeitType::class, $arbeitszeit, [
                'form_id' => $arbeitszeit->getId(),
            ]);
            $forms[$arbeitszeit->getId()] = $form->createView(); 
        }
        
        foreach ($forms as $form) {   
            if ($request->getMethod() == 'POST') {
                
                $data = $request->request->all()['arbeitszeit'];
                
                $arbeitszeit =$form->vars['value'];
                $submittedForm = $request->request; 
                $formId = intval($submittedForm->all()['arbeitszeit']['form_id']);
                if ($formId == $arbeitszeit->getId()){

                   


                       
                  
                    if ($_POST['buttonValue'] == 'save') {
                        
                        $dateTime = new DateTime();
                        $array =$data['Eintrittszeit'];
                        $dateTime->setTime($array['hour'], $array['minute']);
                        $arbeitszeit->setEintrittszeit($dateTime);
                        $dateTime = new DateTime();
                        $array =$data['Austrittszeit'];
                        $dateTime->setTime($array['hour'], $array['minute']);
                        $arbeitszeit->setAustrittszeit($dateTime);
                        $entityManager = $doctrine->getManager();
                        $entityManager->persist($arbeitszeit);
                        $entityManager->flush();

                        // Query the database for the saved data
                        $savedData = $arbeitszeitRepository->find($formId);

                        // Verify that the data matches the expected values
                        if ($savedData && $savedData->getId() != null) {
                            $this->addFlash('success', 'Arbeitszeit gespeichert');
                            $users = $userRepository->findAll();
                            $arbeitszeiten = $arbeitszeitRepository->findAll();
                            $forms = [];    
                            foreach ($arbeitszeiten as $arbeitszeit) {
                                $form = $this->createForm(ArbeitszeitType::class, $arbeitszeit, [
                                    'form_id' => $arbeitszeit->getId(),
                                ]);
                                $forms[$arbeitszeit->getId()] = $form->createView(); 
                            }
                        } else {
                            $this->addFlash('danger', 'Fehler beim Speichern der Arbeitszeit');
                        }
                    } 
                    elseif ($_POST['buttonValue'] == 'delate') {
                        
                        $entityManager = $doctrine->getManager();
                        $entityManager->remove($arbeitszeit);
                        $entityManager->flush();
                        $arbeitszeiten = $arbeitszeitRepository->findAll();
        
                        $this->addFlash('success', 'Arbeitszeit erfolgreich entfernt');
                    }
                     
                }
            }    
        }
            
        return $this->render('arbeitszeit/edit.html.twig', [
            'arbeitszeiten' => $arbeitszeiten,
            'form' => $forms,
           
            'users' => $users,
        ]);
        
    
    }
    #[Route('/fehlzeiten', name: 'app_arbeitszeit_fehlzeiten_add', methods: ['GET', 'POST'])]
    public function fehlzeiten_add(UserRepository $userRepository, FehlzeitenRepository $fehlzeitenRepository ,ManagerRegistry $doctrine, ArbeitszeitRepository $arbeitszeitRepository, Request $request): Response
    { 
    	$userId = $request->get('id');
        $user= $userRepository->find($userId);
        $fehlzeiten = $fehlzeitenRepository->findAll();
        $arbeitszeiten = $arbeitszeitRepository->createQueryBuilder('a')
        ->where('a.user = :user')
        ->andWhere('a.Fehlzeit IS NOT NULL')
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult();
        $choices = [];
        foreach ($fehlzeiten as $fehlzeit) {
            $choices[$fehlzeit->getBezeichnung()] = $fehlzeit->getId();
        }
        $form = $this->createFormBuilder(null, [
            'attr' => ['id' => 'fehlzeiten_form']])
        ->add('von', DateType::class,[
            'label' => 'von',
            'data' => new \DateTime()
            ])
        ->add('bis', DateType::class,[
            'label' => 'bis',
            'data' => (new \DateTime())->modify('+1 day')
            ])
        ->add('fehlzeit', ChoiceType::class,[
            'choices' => $choices,
            
            ])
        ->add('save', SubmitType::class)
        ->getForm()
        ;
        $form -> handleRequest($request);
           
        return $this->renderForm('arbeitszeit/new.html.twig', [
            'user' => $user,
            'form' => $form,
            'arbeitszeiten'=> $arbeitszeiten,
        ]);
    }
    #[Route('/fehlzeiten_save', name: 'app_arbeitszeit_fehlzeiten_save', methods: ['GET', 'POST'])]
    public function fehlzeiten_save(ContractDataRepository $contractDataRepository,UserRepository $userRepository, FehlzeitenRepository $fehlzeitenRepository ,ManagerRegistry $doctrine, Request $request): Response
    { 
    	$userId = $request->get('user');
        $user= $userRepository->find($userId);
        $contractDatas = $contractDataRepository->findBy(['user' => $user]);
        $count_contractDatas = count($contractDatas)-1;
        $vertragsstunden = $contractDatas[$count_contractDatas]->getStunden();
        
        $fehlzeit = $request->get('fehlzeit');
        $fehlzeit = $fehlzeitenRepository->find($fehlzeit);
        $von  = $request->get('von');
        $von = $von." 08:00:00";
        $von = new DateTime($von); 
        
        $temp = 8+($vertragsstunden/5);
        
        $end = $von->setTime($temp,00);
        
        $bis  = $request->get('bis'); 
        $bis = new DateTime($bis);
        
        $return ="";    
        while ($von <= $bis) { 
           $Arbeitszeit = new Arbeitszeit();
            $Arbeitszeit->setUser($user);
            $Arbeitszeit->setDatum($von);
            $Arbeitszeit->setFehlzeit($fehlzeit);
            $Arbeitszeit->setEintrittszeit($von);
            $Arbeitszeit->setAustrittszeit($end); 
            $entityManager = $doctrine->getManager();
            $entityManager->persist($Arbeitszeit);
            $entityManager->flush();
            if ($Arbeitszeit->getId()) {
                $return = "Speichern war erfolgreich!";
            } else {
                $return = "Fehler beim Speichern!";
            };
            $von->modify('+1 day');
            $end->modify('+1 day');  
        }
         

        return new JsonResponse ($return);
    }
}
