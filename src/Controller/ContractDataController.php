<?php

namespace App\Controller;

use App\Entity\ContractData;
use App\Entity\UserContrectData;
use App\Form\ContractDataType;
use App\Repository\CompensationTypesRepository;
use App\Repository\ContractDataRepository;
use App\Repository\UserRepository;
use DateTime;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType ;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/contract/data')]
class ContractDataController extends AbstractController
{
    #[Route('/', name: 'app_contract_data_index', methods: ['GET'])]
    public function index(ContractDataRepository $contractDataRepository): Response
    {
        return $this->render('contract_data/index.html.twig', [
            'contract_datas' => $contractDataRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_contract_data_new', methods: ['GET', 'POST'])]
    public function new( UserRepository $userRepository, Request $request, ManagerRegistry $doctrine, ContractDataRepository $contractDataRepository): Response
    {   
       
        $userId = $request->request->all()['userId'];
        
        $user = $userRepository->find($userId);
        $contractDatum = new ContractData();
        $contractDatum->setUser($user);
        
        $form = $this->createForm(ContractDataType::class, $contractDatum);
        $form->handleRequest($request);
    
           
        return $this->renderForm('contract_data/new.html.twig', [
            'contract_datum' => $contractDatum,
            'form' => $form,
        ]);
    }
    #[Route('/new_status', name: 'app_contract_data_new_status', methods: ['GET', 'POST'])]
    public function new_status( Request $request, ManagerRegistry $doctrine, ContractDataRepository $contractDataRepository): Response
    {   
        $data = $request->request->all()['form'];
        $contract = $contractDataRepository->find( $data['id']);
        
        $entityManager = $doctrine->getManager();
        if($data['Status'] != 'deaktiv'){
            $user = $contract->getUser();
            $contractDatas = $contractDataRepository->findBy(['user'=> $user]);
            foreach($contractDatas as $contractData){
                $contractData->setStatus('deaktiv');  
                $entityManager->persist($contractData);
            }
        }
        $contract->setStatus($data['Status']);  
        
        $entityManager->persist($contract);
        
        $entityManager->flush();
        $this->addFlash('success', 'Vertragsdaten Erfolgreich gespeichert');
        return new JsonResponse('success');
    }
    #[Route('/new_save', name: 'app_contract_data_new_save', methods: ['GET', 'POST'])]
    public function new_save( Request $request, ManagerRegistry $doctrine, ContractDataRepository $contractDataRepository): Response
    {    
        $contractDatum = new ContractData();
        $form = $this->createForm(ContractDataType::class, $contractDatum);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $contractDatum->getUser();
            $contractDataRepository->save($contractDatum, true);
            $userContrectData = new UserContrectData($user, $contractDatum);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($userContrectData);
            $entityManager->flush();
            $this->addFlash('success', 'Vertragsdaten Erfolgreich gespeichert');
            return new JsonResponse('success');
        } else{
            $this->addFlash('danger', 'Fehler beim Speichern der Vertragsdaten');
            return new JsonResponse('danger');
        }
    
        
    }
    #[Route('/editSave', name: 'app_contract_data_editSave', methods: ['GET', 'POST'])]
    public function editcontract_dataSave(Request $request,UserRepository $userRepository,CompensationTypesRepository $compensationTypesRepository ,ContractDataRepository $contractDataRepository): Response
    {   
        $data = $request->request->all()['contract_data'];
        $contractData = $contractDataRepository->find($data['id']);
        $contractData->setBezeichnung($data['bezeichnung']);
        $compensationType = $compensationTypesRepository->find($data['CompensationTypes']);
        $contractData->setCompensationTypes($compensationType);
        $contractData->setUrlaub($data['Urlaub']);
        if($data['endDate']['year'] != ''){
        $endDate = new DateTime(); 
        $endDate->setDate($data['endDate']['year'], $data['endDate']['month'], $data['endDate']['day']);
        $contractData->setEndDate($endDate);
        }
        $singDate = new DateTime(); 
        $singDate->setDate($data['singDate']['year'], $data['singDate']['month'], $data['singDate']['day']);
        $contractData->setSingDate($singDate);
        $startDate = new DateTime(); 
        $startDate->setDate($data['startDate']['year'], $data['startDate']['month'], $data['startDate']['day']);
        $contractData->setStartDate($startDate);    
        $contractData->setLohn($data['lohn']);
        $contractData->setStunden($data['stunden']);
        $user= $userRepository->find($data['user']);
        $contractData->setUser($user);

        $contractDataRepository->save($contractData, true);
        $this->addFlash('success', 'Vertragsdaten erfolgreich geändert');
        return new JsonResponse('1');
       
        
    }
    #[Route('/{id}', name: 'app_contract_data_show', methods: ['GET', 'POST'])]
    public function show(ContractData $contractDatum): Response
    {
        return $this->render('contract_data/show.html.twig', [
            'contract_datum' => $contractDatum,
        ]);
    }
    #[Route('/{id}/status', name: 'app_user_status', methods: ['GET', 'POST'])]
    public function status(Request $request, ContractData $contractData): Response
    {   
        $choices = [];
        $status =  $contractData->getStatus();
        if($status == 'aktiv'){
            $choices[] = ['deaktiv'=>'deaktiv'];
        }elseif($status == 'deaktiv'){
           
            $choices[] = ['aktiv'=>'aktiv'];
        }else{
            $choices[] = ['deaktiv'=>'deaktiv'];
            $choices[] = ['aktiv'=>'aktiv'];
        }

        
        $form = $this->createFormBuilder()
        ->add('id', TextType::class, [
            'data' => $contractData->getId(),
            'label' => false,
            
            ])
        ->add('Status', ChoiceType::class, [
            'choices' => $choices,
            'expanded' => true,
            'multiple' => false,
            'label' => 'Mitarbeiterstatus',
        ])
        ->add('Speichern', SubmitType::class)
        ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return new JsonResponse('gesendet');    
        }else{
            return $this->render('contract_data/status.html.twig', [
                'form' => $form->createView(), 
            ]);    
        }
    }
    #[Route('/{id}/edit', name: 'app_contract_data_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContractData $contractDatum, ContractDataRepository $contractDataRepository): Response
    {
        $form = $this->createForm(ContractDataType::class, $contractDatum);
        $form->add('id', HiddenType::class);
        $form->handleRequest($request);

        

        return $this->renderForm('contract_data/edit.html.twig', [
            'contract_datum' => $contractDatum,
            'form' => $form,
        ]);
    }
   
   
}
