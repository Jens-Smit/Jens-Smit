<?php

namespace App\Controller;

use App\Entity\ContractData;
use App\Entity\UserContrectData;
use App\Form\ContractDataType;
use App\Repository\ContractDataRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function new(Request $request, ManagerRegistry $doctrine, ContractDataRepository $contractDataRepository): Response
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
            
            return $this->redirectToRoute('app_contract_data_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('contract_data/new.html.twig', [
            'contract_datum' => $contractDatum,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_contract_data_show', methods: ['GET'])]
    public function show(ContractData $contractDatum): Response
    {
        return $this->render('contract_data/show.html.twig', [
            'contract_datum' => $contractDatum,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contract_data_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContractData $contractDatum, ContractDataRepository $contractDataRepository): Response
    {
        $form = $this->createForm(ContractDataType::class, $contractDatum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contractDataRepository->save($contractDatum, true);

            return $this->redirectToRoute('app_contract_data_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contract_data/edit.html.twig', [
            'contract_datum' => $contractDatum,
            'form' => $form,
        ]);
    }

   
}
