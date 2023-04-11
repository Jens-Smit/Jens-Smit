<?php

namespace App\Controller;

use App\Entity\Dienstplan;
use App\Form\DienstplanType;
use App\Repository\DiensteRepository;
use App\Repository\DienstplanRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dienstplan')]
class DienstplanController extends AbstractController
{
    #[Route('/', name: 'app_dienstplan_index', methods: ['GET'])]
    public function index(DienstplanRepository $dienstplanRepository): Response
    {
        return $this->render('dienstplan/index.html.twig', [
            'dienstplans' => $dienstplanRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_dienstplan_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DienstplanRepository $dienstplanRepository): Response
    {
        $dienstplan = new Dienstplan();
        $form = $this->createForm(DienstplanType::class, $dienstplan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dienstplanRepository->save($dienstplan, true);

            return $this->redirectToRoute('app_dienstplan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dienstplan/new.html.twig', [
            'dienstplan' => $dienstplan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_dienstplan_show', methods: ['GET'])]
    public function show(Dienstplan $dienstplan, UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('dienstplan/show.html.twig', [
            'users'      =>  $users,
            'dienstplan' => $dienstplan,
        ]);
    }
    #[Route('/{id}/dienste', name: 'app_dienst_show', methods: ['GET'])]
    public function dienst_show(Dienstplan $dienstplan,): Response
    {
        dump($dienstplan);
        return $this->render('dienstplan/show.html.twig', [
            'dienstplan' => $dienstplan,
        ]);
    }
    #[Route('/{id}/edit', name: 'app_dienstplan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Dienstplan $dienstplan, DienstplanRepository $dienstplanRepository): Response
    {
        $form = $this->createForm(DienstplanType::class, $dienstplan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dienstplanRepository->save($dienstplan, true);

            return $this->redirectToRoute('app_dienstplan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dienstplan/edit.html.twig', [
            'dienstplan' => $dienstplan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_dienstplan_delete', methods: ['POST'])]
    public function delete(Request $request, Dienstplan $dienstplan, DienstplanRepository $dienstplanRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dienstplan->getId(), $request->request->get('_token'))) {
            $dienstplanRepository->remove($dienstplan, true);
        }

        return $this->redirectToRoute('app_dienstplan_index', [], Response::HTTP_SEE_OTHER);
    }
}
