<?php

namespace App\Controller;

use App\Entity\Arbeitszeit;
use App\Form\ArbeitszeitType;
use App\Repository\ArbeitszeitRepository;
use App\Repository\DiensteRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    #[Route('/test', name: 'app_arbeitszeit_test', methods: ['GET', 'POST'])]
    public function test(UserRepository $userRepository): Response
    {   
        $datas = false; 
     
    return new Response($datas); 
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
        $users = $userRepository->findAll();
        return $this->render('arbeitszeit/amendment.html.twig', [
            'data' => $data ,
            'users' => $users ,
        ]);
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
            }    
        }
            
        return $this->render('arbeitszeit/edit.html.twig', [
            'arbeitszeiten' => $arbeitszeiten,
            'form' => $forms,
            'users' => $users,
        ]);
        
    
    }
    
    
    
}
