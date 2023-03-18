<?php

namespace App\Controller;

use App\Entity\ItemCategories;
use App\Form\ItemCategoriesType;
use App\Repository\ItemCategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/item/categories')]
class ItemCategoriesController extends AbstractController
{
    #[Route('/', name: 'app_item_categories_index', methods: ['GET'])]
    public function index(ItemCategoriesRepository $itemCategoriesRepository): Response
    {
        return $this->render('item_categories/index.html.twig', [
            'item_categories' => $itemCategoriesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_item_categories_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ItemCategoriesRepository $itemCategoriesRepository): Response
    {
        $itemCategory = new ItemCategories();
        $form = $this->createForm(ItemCategoriesType::class, $itemCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itemCategoriesRepository->save($itemCategory, true);

            return $this->redirectToRoute('app_item_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('item_categories/new.html.twig', [
            'item_category' => $itemCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_item_categories_show', methods: ['GET'])]
    public function show(ItemCategories $itemCategory): Response
    {
        return $this->render('item_categories/show.html.twig', [
            'item_category' => $itemCategory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_item_categories_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ItemCategories $itemCategory, ItemCategoriesRepository $itemCategoriesRepository): Response
    {
        $form = $this->createForm(ItemCategoriesType::class, $itemCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itemCategoriesRepository->save($itemCategory, true);

            return $this->redirectToRoute('app_item_categories_index', [], Response::HTTP_SEE_OTHER);
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
