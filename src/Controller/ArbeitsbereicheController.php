<?php

namespace App\Controller;

use App\Entity\Arbeitsbereiche;
use App\Form\ArbeitsbereicheType;
use App\Repository\ArbeitsbereicheRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/arbeitsbereiche')]
class ArbeitsbereicheController extends AbstractController
{
    #[Route('/', name: 'app_arbeitsbereiche_index', methods: ['GET'])]
    public function index(ArbeitsbereicheRepository $arbeitsbereicheRepository): Response
    {
        return $this->render('arbeitsbereiche/index.html.twig', [
            'arbeitsbereiches' => $arbeitsbereicheRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_arbeitsbereiche_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArbeitsbereicheRepository $arbeitsbereicheRepository): Response
    {
        $arbeitsbereiche = new Arbeitsbereiche();
        $form = $this->createForm(ArbeitsbereicheType::class, $arbeitsbereiche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $arbeitsbereicheRepository->save($arbeitsbereiche, true);

            return $this->redirectToRoute('app_arbeitsbereiche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('arbeitsbereiche/new.html.twig', [
            'arbeitsbereiche' => $arbeitsbereiche,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_arbeitsbereiche_show', methods: ['GET'])]
    public function show(Arbeitsbereiche $arbeitsbereiche): Response
    {
        return $this->render('arbeitsbereiche/show.html.twig', [
            'arbeitsbereiche' => $arbeitsbereiche,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_arbeitsbereiche_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Arbeitsbereiche $arbeitsbereiche, ArbeitsbereicheRepository $arbeitsbereicheRepository): Response
    {
        $form = $this->createForm(ArbeitsbereicheType::class, $arbeitsbereiche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $arbeitsbereicheRepository->save($arbeitsbereiche, true);

            return $this->redirectToRoute('app_arbeitsbereiche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('arbeitsbereiche/edit.html.twig', [
            'arbeitsbereiche' => $arbeitsbereiche,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_arbeitsbereiche_delete', methods: ['POST'])]
    public function delete(Request $request, Arbeitsbereiche $arbeitsbereiche, ArbeitsbereicheRepository $arbeitsbereicheRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$arbeitsbereiche->getId(), $request->request->get('_token'))) {
            $arbeitsbereicheRepository->remove($arbeitsbereiche, true);
        }

        return $this->redirectToRoute('app_arbeitsbereiche_index', [], Response::HTTP_SEE_OTHER);
    }
}
