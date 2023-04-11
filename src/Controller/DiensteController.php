<?php

namespace App\Controller;

use App\Entity\Dienste;
use App\Form\DiensteType;
use App\Repository\DiensteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dienste')]
class DiensteController extends AbstractController
{
    #[Route('/', name: 'app_dienste_index', methods: ['GET'])]
    public function index(DiensteRepository $diensteRepository): Response
    {
        return $this->render('dienste/index.html.twig', [
            'dienstes' => $diensteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_dienste_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DiensteRepository $diensteRepository): Response
    {
        $dienste = new Dienste();
        $form = $this->createForm(DiensteType::class, $dienste);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $diensteRepository->save($dienste, true);

            return $this->redirectToRoute('app_dienste_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dienste/new.html.twig', [
            'dienste' => $dienste,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_dienste_show', methods: ['GET'])]
    public function show(Dienste $dienste): Response
    {
        return $this->render('dienste/show.html.twig', [
            'dienste' => $dienste,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_dienste_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Dienste $dienste, DiensteRepository $diensteRepository): Response
    {
        $form = $this->createForm(DiensteType::class, $dienste);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $diensteRepository->save($dienste, true);

            return $this->redirectToRoute('app_dienste_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dienste/edit.html.twig', [
            'dienste' => $dienste,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_dienste_delete', methods: ['POST'])]
    public function delete(Request $request, Dienste $dienste, DiensteRepository $diensteRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dienste->getId(), $request->request->get('_token'))) {
            $diensteRepository->remove($dienste, true);
        }

        return $this->redirectToRoute('app_dienste_index', [], Response::HTTP_SEE_OTHER);
    }
}
