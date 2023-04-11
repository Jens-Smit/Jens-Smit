<?php

namespace App\Controller;

use App\Entity\ObjektCategories;
use App\Form\ObjektCategoriesType;
use App\Repository\ObjektCategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/objekt/categories')]
class ObjektCategoriesController extends AbstractController
{
    #[Route('/', name: 'app_objekt_categories_index', methods: ['GET'])]
    public function index(ObjektCategoriesRepository $objektCategoriesRepository): Response
    {
        return $this->render('objekt_categories/index.html.twig', [
            'objekt_categories' => $objektCategoriesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_objekt_categories_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ObjektCategoriesRepository $objektCategoriesRepository): Response
    {
        $objektCategory = new ObjektCategories();
        $form = $this->createForm(ObjektCategoriesType::class, $objektCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $objektCategoriesRepository->save($objektCategory, true);

            return $this->redirectToRoute('app_objekt_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('objekt_categories/new.html.twig', [
            'objekt_category' => $objektCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_objekt_categories_show', methods: ['GET'])]
    public function show(ObjektCategories $objektCategory): Response
    {
        return $this->render('objekt_categories/show.html.twig', [
            'objekt_category' => $objektCategory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_objekt_categories_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ObjektCategories $objektCategory, ObjektCategoriesRepository $objektCategoriesRepository): Response
    {
        $form = $this->createForm(ObjektCategoriesType::class, $objektCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $objektCategoriesRepository->save($objektCategory, true);

            return $this->redirectToRoute('app_objekt_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('objekt_categories/edit.html.twig', [
            'objekt_category' => $objektCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_objekt_categories_delete', methods: ['POST'])]
    public function delete(Request $request, ObjektCategories $objektCategory, ObjektCategoriesRepository $objektCategoriesRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$objektCategory->getId(), $request->request->get('_token'))) {
            $objektCategoriesRepository->remove($objektCategory, true);
        }

        return $this->redirectToRoute('app_objekt_categories_index', [], Response::HTTP_SEE_OTHER);
    }
}
