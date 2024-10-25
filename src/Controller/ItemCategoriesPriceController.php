<?php

namespace App\Controller;

use App\Entity\ItemCategoriesPrice;
use App\Form\ItemCategoriesPriceType;
use App\Repository\ItemCategoriesPriceRepository;
use App\Repository\ItemCategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ItemCategoriesPriceController extends AbstractController
{


    #[Route('/item/categories/price/{id}', name: 'app_item_categories_price_new', methods: ['GET', 'POST'])]
    public function price(Request $request, ItemCategoriesPriceRepository $itemCategoriesPriceRepository, ItemCategoriesRepository $itemCategoriesRepository): Response
    {
        $itemCatogorieId = $request->attributes->get('id');
        $itemCategoriesPrice = new ItemCategoriesPrice();
        $itemCatogorie = $itemCategoriesRepository->find($itemCatogorieId);
        $itemCategoriesPrice->setItemCategory($itemCatogorie);
        $form = $this->createForm(ItemCategoriesPriceType::class, $itemCategoriesPrice);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $neuerStarttermin = $form->getData()->getStart();
            $neuerEndtermin = $form->getData()->getEnd();
            $neuerPreis = $form->getData()->getPrice();
            $itemCategoriesPriceRepository->updateOrCreatePrice($neuerPreis, $neuerStarttermin, $neuerEndtermin, $itemCatogorie);
            //   dump($itemCategoriesPriceRepository->updateOrCreatePrice($neuerPreis, $neuerStarttermin, $neuerEndtermin, $itemCatogorie));
            // $itemCategoriesPriceRepository->save($itemCategoriesPrice, true);

            return $this->renderForm('item_categories_price/new.html.twig', [
                'item_categories_price' => $itemCategoriesPrice,
                'form' => $form,
            ]);
        }

        return $this->renderForm('item_categories_price/new.html.twig', [
            'item_categories_price' => $itemCategoriesPrice,
            'form' => $form,
        ]);
    }
    #[Route('/item/categories/price/{id}/ajax', name: 'app_item_categories_price_ajax', methods: ['GET', 'POST'])]
    public function pricesAtMonth(Request $request, ItemCategoriesPriceRepository $itemCategoriesPriceRepository, ItemCategoriesRepository $itemCategoriesRepository): Response
    {
        $itemCatogorieId = $request->attributes->get('id');
        $year =  $_POST['year'];
        $month = $_POST['month'] + 1;
        $preices = $itemCategoriesPriceRepository->getPricesForMonth($month, $year, $itemCatogorieId);
        return new JsonResponse($preices);
    }
}
