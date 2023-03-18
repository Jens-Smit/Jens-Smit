<?php

namespace App\Controller;

use App\Entity\Recomonsation;
use App\Form\RecomonsationType;
use App\Repository\ObjektRepository;
use App\Repository\RecomonsationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recomonsation')]
class RecomonsationController extends AbstractController
{
    #[Route('/', name: 'app_recomonsation_index', methods: ['GET'])]
    public function index(RecomonsationRepository $recomonsationRepository): Response
    {
        return $this->render('recomonsation/index.html.twig', [
            'recomonsations' => $recomonsationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_recomonsation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RecomonsationRepository $recomonsationRepository): Response
    {
        $recomonsation = new Recomonsation();
        $form = $this->createForm(RecomonsationType::class, $recomonsation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recomonsationRepository->save($recomonsation, true);

            return $this->redirectToRoute('app_recomonsation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('recomonsation/new.html.twig', [
            'recomonsation' => $recomonsation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_recomonsation_show', methods: ['GET', 'POST'])]
    public function show(string $id,ObjektRepository $objektRepository,Request $request, RecomonsationRepository $recomonsationRepository): Response
    {
        $recomonsation = new Recomonsation();
        $form = $this->createForm(RecomonsationType::class, $recomonsation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recomonsationRepository->save($recomonsation, true);
            return $this->redirectToRoute('app_recomonsation_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('recomonsation/index.html.twig', [
            'recomonsations' => $objektRepository->findRecomodation($id),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_recomonsation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recomonsation $recomonsation, RecomonsationRepository $recomonsationRepository): Response
    {
        $form = $this->createForm(RecomonsationType::class, $recomonsation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recomonsationRepository->save($recomonsation, true);

            return $this->redirectToRoute('app_recomonsation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('recomonsation/edit.html.twig', [
            'recomonsation' => $recomonsation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_recomonsation_delete', methods: ['POST'])]
    public function delete(Request $request, Recomonsation $recomonsation, RecomonsationRepository $recomonsationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$recomonsation->getId(), $request->request->get('_token'))) {
            $recomonsationRepository->remove($recomonsation, true);
        }

        return $this->redirectToRoute('app_recomonsation_index', [], Response::HTTP_SEE_OTHER);
    }
}
