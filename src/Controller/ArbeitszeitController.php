<?php

namespace App\Controller;

use App\Entity\Arbeitszeit;
use App\Repository\ArbeitszeitRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Time;

#[Route('/arbeitszeit')]        
class ArbeitszeitController extends AbstractController
{
    #[Route('/', name: 'app_arbeitszeit', methods: ['GET'])]
    public function index(ArbeitszeitRepository $arbeitszeitRepository): Response
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $Arbeitszeit = $arbeitszeitRepository->findBy( ['user' => $user, 'Austrittszeit' => null ]);
       
        return $this->render('arbeitszeit/index.html.twig', [
            'user' => $user ,
            'Arbeitszeiten' => $Arbeitszeit ,

        ]);
    }
    #[Route('/checkIn', name: 'app_arbeitszeit_checkIn', methods: ['GET'])]
    public function checkIn(ManagerRegistry $doctrine,): Response
    {
        
        $time = new DateTime(date("H:i:s"));
        $day = new DateTime(date("Y-m-d"));
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $Arbeitszeit = new Arbeitszeit();
        $Arbeitszeit->setUser($user);
        $Arbeitszeit->setEintrittszeit($time);
        $Arbeitszeit->setDatum($day);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($Arbeitszeit);
        $entityManager->flush();
        if ($entityManager->contains($Arbeitszeit)) {
            $datas = true;
        } else {
            $datas = false;
        }
        return new JsonResponse($datas);
    }
    #[Route('/checkOut', name: 'app_arbeitszeit_checkOut', methods: ['GET'])]
    public function checkOut(ArbeitszeitRepository $arbeitszeitRepository,ManagerRegistry $doctrine): Response
    {
        
        $time = new DateTime(date("H:i:s"));
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $Arbeitszeiten = $arbeitszeitRepository->findBy( ['user' => $user, 'Austrittszeit' => null ]);
        $Arbeitszeit = $Arbeitszeiten[0];
        $Arbeitszeit->setUser($user);
        $Arbeitszeit->setAustrittszeit($time);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($Arbeitszeit);
        $entityManager->flush();
        if ($entityManager->contains($Arbeitszeit)) {
            $datas = true;
        } else {
            $datas = false;
        }
        return new JsonResponse($datas);
    }
}
