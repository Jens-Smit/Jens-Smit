<?php

namespace App\Controller;

use App\Entity\Discount;
use App\Form\DiscountType;
use App\Repository\DiscountRepository;
use App\Repository\ItemCategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DiscountController extends AbstractController
{
    #[Route('item/categories/discount/{categoryId}', name: 'app_discount_index', methods: ['GET'])]
    public function index(DiscountRepository $discountRepository,Request $request,ItemCategoriesRepository $itemCategoriesRepository): Response
    {
        $itemCategoryId = $request->attributes->get('categoryId');
        $itemCategories = $itemCategoriesRepository->find($itemCategoryId);
       
        $ItemCategoriesPrices =$itemCategories->getItemCategoriesPrices();
        
        $discounts = [];
        foreach ($ItemCategoriesPrices as $itemCategoryPrice) {
            $discounts += $discountRepository->findBy(['itemCategoriePrice' => $itemCategoryPrice]);
        }
        
        return $this->render('discount/index.html.twig', [
            'discounts' => $discounts,
            'categoryId' => $itemCategories
        ]);
    }

    #[Route('item/categories/discount/{categoryId}/new', name: 'app_discount_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DiscountRepository $discountRepository): Response
    {
        $itemCategoryId = $request->attributes->get('categoryId');
        $discount = new Discount();
        $form = $this->createForm(DiscountType::class, $discount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $discountRepository->save($discount, true);

            return $this->redirectToRoute('app_discount_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('discount/new.html.twig', [
            'discount' => $discount,
            'form' => $form,
            'categoryId' => $itemCategoryId
        ]);
    }

    #[Route('item/categories/discount//{id}', name: 'app_discount_show', methods: ['GET'])]
    public function show(Discount $discount): Response
    {
        return $this->render('discount/show.html.twig', [
            'discount' => $discount,
        ]);
    }

    #[Route('item/categories/discount//{id}/edit', name: 'app_discount_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Discount $discount, DiscountRepository $discountRepository): Response
    {
        $form = $this->createForm(DiscountType::class, $discount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $discountRepository->save($discount, true);

            return $this->redirectToRoute('app_discount_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('discount/edit.html.twig', [
            'discount' => $discount,
            'form' => $form,
        ]);
    }

    #[Route('item/categories/discount//{id}', name: 'app_discount_delete', methods: ['POST'])]
    public function delete(Request $request, Discount $discount, DiscountRepository $discountRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$discount->getId(), $request->request->get('_token'))) {
            $discountRepository->remove($discount, true);
        }

        return $this->redirectToRoute('app_discount_index', [], Response::HTTP_SEE_OTHER);
    }
}
