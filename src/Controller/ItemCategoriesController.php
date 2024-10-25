<?php

namespace App\Controller;
use App\Entity\ItemCategories;
use App\Form\ItemCategoriesType;
use App\Repository\CompanyRepository;
use App\Repository\ItemCategoriesRepository;
use App\Repository\ObjektRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/item/categories')]
class ItemCategoriesController extends AbstractController
{
    #[Route('/{objectId}', name: 'app_item_categories_index', methods: ['GET','POST'])]
    public function index( Request $request, CompanyRepository $companyRepository, ObjektRepository $objectRepository): Response
    {
       $objectId = $request->attributes->get('objectId');
        // Aktuellen Benutzer abrufen
       $user = $this->container->get('security.token_storage')->getToken()->getUser();
        // Unternehmen für Benutzer abrufen - Admin eines Unternehmens
       
        
        if(!empty($user->getObjekt()) && $user->getObjekt()->getId() == $objectId){
            $itemCategories = $user->getObjekt()->getItemCategories();
            return $this->render('item_categories/index.html.twig', [
             'item_categories' => $itemCategories,
             ]);  
        } else {
            $objects = $user->getCompany()->getObjekts();
            $RentitemCategories = '';
            foreach($objects as $object){
             //   dump($object->getId());
                if($object->getId() == $objectId){
                    $RentitemCategories = $object->getItemCategories();
                   
                }

            }
             
            return $this->render('item_categories/index.html.twig', [
                'item_categories' => $RentitemCategories,
                ]);
        }
    }
    
  
    #[Route('/{objectId}/new', name: 'app_item_categories_new', methods: ['GET', 'POST'])]
    public function new(Request $request,ObjektRepository $objectRepository ,ItemCategoriesRepository $itemCategoriesRepository): Response
    {
        $objectId = $request->attributes->get('objectId');
        $object = $objectRepository->find($objectId);
        $itemCategory = new ItemCategories();
        $itemCategory->setObjekt($object);
        $form = $this->createForm(ItemCategoriesType::class, $itemCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itemCategoriesRepository->save($itemCategory, true);
            return $this->redirect('/item/categories/'.$itemCategory->getObjekt()->getId());
            
        }

        return $this->renderForm('item_categories/new.html.twig', [
            'item_category' => $itemCategory,
            'form' => $form,
            'objectId' => $objectId,
        ]);
    }

    

    #[Route('/{id}/edit', name: 'app_item_categories_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ItemCategories $itemCategory, ItemCategoriesRepository $itemCategoriesRepository): Response
    {
        $form = $this->createForm(ItemCategoriesType::class, $itemCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itemCategoriesRepository->save($itemCategory, true);
            return $this->redirect('/item/categories/'.$itemCategory->getObjekt()->getId());
          //  return $this->redirectToRoute('app_objekt_index', [], Response::HTTP_SEE_OTHER);
           
        }

        return $this->renderForm('item_categories/edit.html.twig', [
            'item_category' => $itemCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_item_categories_delete', methods: ['POST'])]
    public function delete(Request $request, ItemCategories $itemCategory, ItemCategoriesRepository $itemCategoriesRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$itemCategory->getId(), $request->request->get('_token'))) {
            $itemCategoriesRepository->remove($itemCategory, true);
        }

        return $this->redirectToRoute('app_item_categories_index', [], Response::HTTP_SEE_OTHER);
    }
   
}
