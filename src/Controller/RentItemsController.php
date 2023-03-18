<?php

namespace App\Controller;

use App\Entity\Area;
use App\Entity\Objekt;
use App\Entity\RentItems;
use App\Form\RentItemsType;
use App\Repository\AreaRepository;
use App\Repository\CompanyRepository;
use App\Repository\ObjektRepository;
use App\Repository\RentItemsRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/Items')]
class RentItemsController extends AbstractController
{
    #[Route('/', name: 'app_rent_items_index', methods: ['GET'])]
    public function index(ObjektRepository $objektRepository,CompanyRepository $companyRepository, RentItemsRepository $rentItemsRepository): Response
    {   // Aktuellen Benutzer abrufen
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        // Unternehmen für Benutzer abrufen - Admin eines Unternehmens
        $companies = $companyRepository->findBy(['onjekt_admin' => $user]);

        if (empty($companies)) {
            // Wenn der Benutzer kein Admin eines Unternehmens ist, verwende das eigene Unternehmen
            $objekt  = $this->container->get('security.token_storage')->getToken()->getUser()->getObjekt();
            $items = $rentItemsRepository->findBy(['objekt'=> $objekt]);
            
            return $this->render('rent_items/index.html.twig', [   
                'rent_items' => $items
            ]);
        } else {
            // Alle Objekte für die Unternehmen mit einer einzigen Abfrage mittels IN-Operator abrufen
            $companyIds = array_map(function($company) { return $company->getId(); }, $companies);
            $objekts = $objektRepository->findBy((['company' => $companyIds]));
            
            $items = [];
            foreach( $objekts as $objekt){
                // Mietgegenstände für jedes Objekt abrufen und Ergebnisse in Array speichern
                $items = array_merge($items, $rentItemsRepository->findBy(['objekt'=> $objekt]));
            }
            
            return $this->render('rent_items/index.html.twig', [   
                'rent_items' => $items
            ]);
        }
    }

    #[Route('/new', name: 'app_rent_items_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RentItemsRepository $rentItemsRepository): Response
    {
        $rentItem = new RentItems();
        $form = $this->createForm(RentItemsType::class, $rentItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rentItemsRepository->save($rentItem, true);

            return $this->redirectToRoute('app_rent_items_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('rent_items/new.html.twig', [
            'rent_item' => $rentItem,
            'form' => $form,
        ]);
    }
    
    #[Route('/{id}', name: 'app_rent_items_show', methods: ['GET'])]
    public function show(RentItems $rentItem): Response
    {
        return $this->render('rent_items/show.html.twig', [
            'rent_item' => $rentItem,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rent_items_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RentItems $rentItem, RentItemsRepository $rentItemsRepository): Response
    {
        $form = $this->createForm(RentItemsType::class, $rentItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rentItemsRepository->save($rentItem, true);

            return $this->redirectToRoute('app_rent_items_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('rent_items/edit.html.twig', [
            'rent_item' => $rentItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rent_items_delete', methods: ['POST'])]
    public function delete(Request $request, RentItems $rentItem, RentItemsRepository $rentItemsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rentItem->getId(), $request->request->get('_token'))) {
            $rentItemsRepository->remove($rentItem, true);
        }

        return $this->redirectToRoute('app_rent_items_index', [], Response::HTTP_SEE_OTHER);
    }
}
