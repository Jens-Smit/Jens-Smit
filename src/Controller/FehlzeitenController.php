<?php

namespace App\Controller;

use App\Entity\Fehlzeiten;
use App\Form\FehlzeitenType;
use App\Repository\FehlzeitenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/fehlzeiten')]
class FehlzeitenController extends AbstractController
{
    #[Route('/', name: 'app_fehlzeiten_index', methods: ['GET'])]
    public function index(FehlzeitenRepository $fehlzeitenRepository): Response
    {
        return $this->render('fehlzeiten/index.html.twig', [
            'fehlzeitens' => $fehlzeitenRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_fehlzeiten_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FehlzeitenRepository $fehlzeitenRepository): Response
    {
        $fehlzeiten = new Fehlzeiten();
        $form = $this->createForm(FehlzeitenType::class, $fehlzeiten);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fehlzeitenRepository->save($fehlzeiten, true);

            return $this->redirectToRoute('app_fehlzeiten_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('fehlzeiten/new.html.twig', [
            'fehlzeiten' => $fehlzeiten,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fehlzeiten_show', methods: ['GET'])]
    public function show(Fehlzeiten $fehlzeiten): Response
    {
        return $this->render('fehlzeiten/show.html.twig', [
            'fehlzeiten' => $fehlzeiten,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_fehlzeiten_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fehlzeiten $fehlzeiten, FehlzeitenRepository $fehlzeitenRepository): Response
    {
        $form = $this->createForm(FehlzeitenType::class, $fehlzeiten);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fehlzeitenRepository->save($fehlzeiten, true);

            return $this->redirectToRoute('app_fehlzeiten_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('fehlzeiten/edit.html.twig', [
            'fehlzeiten' => $fehlzeiten,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fehlzeiten_delete', methods: ['POST'])]
    public function delete(Request $request, Fehlzeiten $fehlzeiten, FehlzeitenRepository $fehlzeitenRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fehlzeiten->getId(), $request->request->get('_token'))) {
            $fehlzeitenRepository->remove($fehlzeiten, true);
        }

        return $this->redirectToRoute('app_fehlzeiten_index', [], Response::HTTP_SEE_OTHER);
    }
}
