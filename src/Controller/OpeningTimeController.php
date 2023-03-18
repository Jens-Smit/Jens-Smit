<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OpeningTimeController extends AbstractController
{
    #[Route('/opening/time', name: 'app_opening_time')]
    public function index(): Response
    {
        return $this->render('opening_time/index.html.twig', [
            'controller_name' => 'OpeningTimeController',
        ]);
    }
}
