<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard',  methods: ['GET', 'POST'])]
    public function index(): Response
    {
            
            $text = "test";
      
        

        

        return $this->render('dashboard/index.html.twig', [
            'controller_name' =>  $text ,
        ]);
    }
}
