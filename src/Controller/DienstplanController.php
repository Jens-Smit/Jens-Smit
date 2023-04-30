<?php

namespace App\Controller;

use GuzzleHttp\Client;
use App\Entity\Dienste;
use App\Entity\Dienstplan;
use App\Entity\User;
use App\Form\DiensteType;
use App\Form\DienstplanType;
use App\Repository\DiensteRepository;
use App\Repository\DienstplanRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\DBAL\Types\DateType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/dienstplan')]
class DienstplanController extends AbstractController
{
    #[Route('/', name: 'app_dienstplan_index', methods: ['GET'])]
    public function index(DienstplanRepository $dienstplanRepository): Response
    {
        return $this->render('dienstplan/index.html.twig', [
            'dienstplans' => $dienstplanRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_dienstplan_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DienstplanRepository $dienstplanRepository): Response
    {
        $dienstplan = new Dienstplan();
        $form = $this->createForm(DienstplanType::class, $dienstplan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dienstplanRepository->save($dienstplan, true);

            return $this->redirectToRoute('app_dienstplan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dienstplan/new.html.twig', [
            'dienstplan' => $dienstplan,
            'form' => $form,
        ]);
    }
    #[Route('/dienste_save', name: 'app_dienst_save',  methods: ['POST','GET'])]
    public function dienste_save(Request $request,DiensteRepository $diensteRepository ,DienstplanRepository $dienstplanRepository,UserRepository $userRepository, ManagerRegistry $doctrine ): Response
    {
      
        
        // Überprüfen, ob das Formular gesendet wurde und der HTTP-Verb POST ist
        if ($request->getMethod() == 'POST') {
            $kommen =  $_POST['kommen'] ;
            $gehen = $_POST['gehen'] ;
            $user = $_POST['user'];
            $dienstplan =  $_POST['dienstplan'] ;
        
            // Holen Sie sich die POST-Parameter aus dem Formular
            $gehen = new DateTime($gehen);
            $kommen = new DateTime($kommen);
           // $gehen = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $gehen);
            
            // Holen Sie sich das Dienstplan-Objekt anhand der ID
          $dienstplan = $dienstplanRepository->findOneBy(['bezeichnung'=>$dienstplan]);
            $user = $userRepository->find($user);
            // Erstellen Sie ein neues Dienst-Objekt
            $dienste = new Dienste();
            
            // Setzen Sie die Werte des Dienst-Objekts
            $dienste->setUser($user);
            $dienste->setDienstplan($dienstplan);
            $dienste->setKommen($kommen);
            $dienste->setGehen($gehen);
            $diensteRepository->save($dienste, true);
            
            // Weiterleiten zur Erfolgsseite
            return new Response('erfolgreichlich');
        }
        
        // Wenn kein POST-Request gesendet wurde, zeige das Formular an
        
         return new Response('test');
        
    }
    #[Route('/dienste', name: 'app_dienst_new',  methods: ['POST','GET'])]
    public function dienste_new(DiensteRepository $diensteRepository, DienstplanRepository $dienstplanRepository,UserRepository $userRepository ,Request $request): Response
    {   
        $dienste = new Dienste();  
        // Instantiate Guzzle HTTP client
       
        if(count($request->query->all()) != 0){
            $data = ($request->query->all());        
            $user= $data['user'];  
            $dienstplan= $data['dienstplan']; 
            $dienstplan= $dienstplanRepository->find($dienstplan);
            $date= $data['date']." 00:00:00"; 
            $tag = new \DateTime($date);
            $next_day= new \DateTime(date("Y-m-d H:i:s",strtotime($data['date']." 00:00:00")+(24*60*60))); 
            $kommen= DateTime::createFromFormat('Y-m-d H:i:s', $date);
            $user = $userRepository->findOneBy(["email" => $user]);
            $dienste->setKommen($kommen);
            $dienste->setGehen($kommen);
            $dienste->setUser($user);
            $dienste->setDienstplan($dienstplan);
        }  
        $form = $this->createForm(DiensteType::class, $dienste); 
        $plan_dienste = $diensteRepository->createQueryBuilder('d')
        ->where('d.user = :user')
        ->andWhere('d.kommen >= :kommen')
        ->andWhere('d.gehen <= :gehen')
        ->setParameter('user', $user->getId())
        ->setParameter('kommen', $tag->format("Y-m-d H:i:s"))
        ->setParameter('gehen', $next_day->format("Y-m-d H:i:s"))
        ->getQuery()
        ->getResult();
        if(count($request->query->all()) != 0){
          
        $form->get('kommen')->setData($kommen);
        $form->get('gehen')->setData($kommen);
        $form->get('user')->setData($user);
        $form->get('dienstplan')->setData($dienstplan);
        }
        $form -> handleRequest($request);
       
        if ($this->isCsrfTokenValid('delete'.$dienste->getId(), $request->request->get('_token'))) {
             $dienstplan = $dienste->getDienstplan()->getId();
           $diensteRepository->remove($dienste, true);
        }

        $filePath = $this->getFilePath($dienstplan->getId());
        $data = file_get_contents($filePath);
        $dataArray = json_decode($data, true);
       
        return $this->renderForm('dienstplan/new_dienste.html.twig', [
           
            'datas' => $dataArray,
            'plan_dienste' => $plan_dienste,
            'dienste' => $dienste,
            'form' => $form,
        ]);
      
    }
    #[Route('/{id}', name: 'app_dienstplan_show', methods: ['POST','GET'])]
    public function show(Dienstplan $dienstplan): Response
    {
        $filePath = $this->getFilePath($dienstplan->getId());
        $data = json_decode(file_get_contents($filePath), true);
        $kw = date("W");
        $users = $dienstplan->getUser();
        $client = new Client();
         // Make GET request to Nager.Date API
        $response = $client->request('GET', 'https://date.nager.at/api/v3/publicholidays/2023/DE');

        // Decode JSON response
        $holidays = json_decode($response->getBody()->getContents(), true);

        // Create array of holidays with date as key and name as value
        $formattedHolidays = [];
        foreach ($holidays as $holiday) {
            $formattedHolidays[$holiday['date']] = $holiday['name'];
        }

        return $this->render('dienstplan/show.html.twig', [
            'holidays'=> $formattedHolidays,
            'data' => $data,
            'users'      =>  $users,
            'dienstplan' => $dienstplan,
            'kw' => $kw,
        ]);
    }
    #[Route('/{id}/create', name: 'app_dienstplan_create', methods: ['POST','GET'])]
    public function create(Dienstplan $dienstplan): Response
    {
        $filePath = $this->getFilePath($dienstplan->getId());
        $data = json_decode(file_get_contents($filePath), true);
        $kw = date("W");
        $users = $dienstplan->getUser();
        $client = new Client();
         // Make GET request to Nager.Date API
        $response = $client->request('GET', 'https://date.nager.at/api/v3/publicholidays/2023/DE');

        // Decode JSON response
        $holidays = json_decode($response->getBody()->getContents(), true);

        // Create array of holidays with date as key and name as value
        $formattedHolidays = [];
        foreach ($holidays as $holiday) {
            $formattedHolidays[$holiday['date']] = $holiday['name'];
        }

        return $this->render('dienstplan/create.html.twig', [
            'holidays'=> $formattedHolidays,
            'data' => $data,
            'users'      =>  $users,
            'dienstplan' => $dienstplan,
            'kw' => $kw,
        ]);
    }
    #[Route('/{id}/edit', name: 'app_dienstplan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Dienstplan $dienstplan, DienstplanRepository $dienstplanRepository): Response
    {
        $form = $this->createForm(DienstplanType::class, $dienstplan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dienstplanRepository->save($dienstplan, true);

            return $this->redirectToRoute('app_dienstplan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dienstplan/edit.html.twig', [
            'dienstplan' => $dienstplan,
            'form' => $form,
        ]);
    }
    private function getFilePath($id)
    {
        $filename = sprintf('Dienstplan_%d.json', $id);
        $filePath = $this->getParameter('kernel.project_dir') . '/public/data/dienstplan/' . $filename;

        if (!file_exists($filePath)) {
            $data = []; // initial data to write
            $jsonData = json_encode($data);
            if (file_put_contents($filePath, $jsonData) === false) {
                throw new \RuntimeException(sprintf('Unable to create the file "%s".', $filePath));
            }
        }

        return $filePath;
    }
    #[Route('/{id}/edit_Dienste_PV', name: 'app_edit_Dienste_PV', methods: ['GET', 'POST'])]
    public function edit_Dienste_PV(Request $request, $id, DienstplanRepository $dienstplanRepository): Response
    { 
        $filePath = $this->getFilePath($id);
        if (!file_exists($filePath)) {
            return new JsonResponse(['error' => 'File not found'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode(file_get_contents($filePath), true);
      
       
        // Schlüssel hinzufügen
        $new_array = [];
        foreach ($data as $key => $value) {
            $new_array[$key] = $value;
        }
       // dump($new_array);
        // Add fields for "Kommen" and "Gehen" for each ID
        $formBuilder = $this->createFormBuilder($new_array);
        foreach ($new_array as $key => $value) {
            $formBuilder
                ->add($key.'_kommen', TimeType::class, [
                    'label' => 'Kommen '.$key,
                    'attr' => ['class' => 'form-control'],
                    'data' => new \DateTime($new_array[$key]['kommen'])  
                ])
                ->add($key.'_gehen', TimeType::class, [
                    'label' => 'Gehen '.$key,
                    'attr' => ['class' => 'form-control'],
                    'data' => new \DateTime($new_array[$key]['gehen'])
                ]);
            }$key= $key+1;
            $formBuilder
                ->add($key.'_kommen', TimeType::class, [
                    'label' => 'Neu Kommen ',
                    'attr' => ['class' => 'form-control']
                ])
                ->add($key.'_gehen', TimeType::class, [
                    'label' => 'Neu Gehen ',
                    'attr' => ['class' => 'form-control']
                ]);
        $form = $formBuilder->getForm();

        $form->handleRequest($request);
        $dienstplan = $dienstplanRepository->find($id);
        if ($form->isSubmitted() && $form->isValid()) {
            // Update data with submitted values
            $submittedData = $form->getData();
            $count_array = 0;
            foreach ($submittedData as $key => $value){
                if (is_int($key)){
                    $count_array++;
                }
            }
            $data = array_slice($submittedData, $count_array);
            $newArray = [];
            foreach ($data as $key => $value){
                $parts = explode('_', $key);
                $index = $parts[0];
                $subKey = $parts[1];
                $time = $value->format('H:i');
                if(!isset($newArray[$index])){
                    $newArray[$index] = [];
                }
                $newArray[$index][$subKey] = $time;
            }
            $lastItem = end($newArray);
            if($lastItem['kommen'] =='00:00'){
                //flashmessage -> kommen Darf nicht 00:00 Sein
                array_splice($newArray, -2);
                file_put_contents($filePath, json_encode($newArray));
                return $this->render('dienstplan/edit_pv.html.twig', [
                    'form' => $form->createView(),
                    'dienstplan' => $dienstplan,
                ]);
            }
            elseif($lastItem['gehen'] =='00:00'){
                //flashmessage -> gehen Darf nicht 00:00 Sein
                array_splice($newArray, -2);
                file_put_contents($filePath, json_encode($newArray));
                return $this->render('dienstplan/edit_pv.html.twig', [
                    'form' => $form->createView(),
                    'dienstplan' => $dienstplan,
                ]);
            }else{
             file_put_contents($filePath, json_encode($newArray));
             return $this->redirectToRoute('app_dienstplan_edit', ['id' => $id]);
            }   
        }
        return $this->render('dienstplan/edit_pv.html.twig', [
            'form' => $form->createView(),
            'dienstplan' => $dienstplan,
        ]); 
    }

    #[Route('/{id}', name: 'app_dienstplan_delete', methods: ['POST'])]
    public function delete(Request $request, Dienstplan $dienstplan, DienstplanRepository $dienstplanRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dienstplan->getId(), $request->request->get('_token'))) {
            $dienstplanRepository->remove($dienstplan, true);
        }

        return $this->redirectToRoute('app_dienstplan_index', [], Response::HTTP_SEE_OTHER);
    }
}
