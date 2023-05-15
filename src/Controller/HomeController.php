<?php

namespace App\Controller;

use App\Repository\ObjektCategoriesRepository;
use App\Repository\ObjektRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Gregwar\CaptchaBundle\Type\CaptchaType;
#[Route('/')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET', 'POST'])]
    public function index(Request $request,MailerInterface $mailer): Response
    {
       
        $form = $this->createFormBuilder(null, [
            'attr' => ['class' => 'w-100']
        ])
        ->add('email', EmailType::class, [
            'attr' => ['class' => 'w-100']
        ])
        ->add('Firmenname', TextType::class, [
            'attr' => ['class' => 'w-100']
        ])
        ->add('Nachricht', TextareaType::class, [
            'attr' => ['class' => 'w-100']
        ])
        ->add('captcha', CaptchaType::class, [
            'label' => 'Bist du ein Computer?',
            
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Anfrage senden',
            'attr' => ['class' => 'btn-info w-100 btn']
        ])
        ->getForm();
        $form -> handleRequest($request);
       

       if ($form->isSubmitted() && $form->isValid()) {
           $data = $form->getData();
           $user_email= $data['email'];
           $Firmenname = $data['Firmenname'];
           $Nachricht = $data['Nachricht'];
           $email = (new Email())
            ->from($user_email)
            ->to('info@tex-mex.de')
            ->subject('Anfrage BETA-User '.$Firmenname )
            ->text($Nachricht)
            ->html('txt');

            $mailer->send($email);
         
       }
        return $this->render('home/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('home/Datenschutz.html.twig');
    }
    #[Route('/contakt', name: 'contakt')]
    public function contakt(Request $request,MailerInterface $mailer): Response
    {
        $form = $this->createFormBuilder(null, [
            'attr' => ['class' => 'w-100']
        ])
        ->add('email', EmailType::class, [
            'attr' => ['class' => 'w-100']
        ])
        ->add('Firmenname', TextType::class, [
            'attr' => ['class' => 'w-100']
        ])
        ->add('Nachricht', TextareaType::class, [
            'attr' => ['class' => 'w-100']
        ])
        ->add('captcha', CaptchaType::class, [
            'label' => 'Bist du ein Computer?',
            
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Anfrage senden',
            'attr' => ['class' => 'btn-info w-100 btn']
        ])
        ->getForm();
        $form -> handleRequest($request);
       

       if ($form->isSubmitted() && $form->isValid()) {
           $data = $form->getData();
           $user_email= $data['email'];
           $Firmenname = $data['Firmenname'];
           $Nachricht = $data['Nachricht'];
           $email = (new Email())
            ->from($user_email)
            ->to('info@tex-mex.de')
            ->subject('Anfrage BETA-User '.$Firmenname )
            ->text($Nachricht)
            ->html('txt');

            $mailer->send($email);
         
       }
        return $this->render('home/contakt.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route('/privacy ', name: 'privacy')]
    public function privacy(): Response
    {
        return $this->render('home/Datenschutz.html.twig');
    }

   /* public function index(Request $request,RentItemsRepository $rentItemsRepository ,ReservationRepository $reservationRepository): Response
    {
        $items = $reservationRepository->findAll();
        $Avform = $this->createFormBuilder()
        ->add('date', DateTimeType::class, [
            'date_label' => 'Datum',
            ])
        ->add('pax', NumberType::class)
        ->add('send', SubmitType::class)
        ->getForm()
        ;
        $Avform->handleRequest($request);

        if ($Avform->isSubmitted()){
            
        $id = 1;
        $eingabe = $Avform->getData();
        $date = $eingabe['date'];
        $datestr = $date->format( 'Y-m-d H:i:s' ); 
        $time_edd= strtotime($datestr)+(60*60);
        $time  = new DateTime();
        $time->setTimestamp($time_edd);
        $timestr = $time->format( 'Y-m-d H:i:s' );
        




        $pax = $eingabe['pax'];
        
        $items = $rentItemsRepository->findfree( $datestr,$timestr, $id, $pax);
           
        $info = 'test';   
        return $this->render('home/index.html.twig', [
            'info' => $info,
            'Avform' => $Avform->createView(),
            'items' => $items,
        ]);

        }
        return $this->render('home/index.html.twig', [
            'info' => 'Alle reservierungen',
            'Avform' => $Avform->createView(),
            'items' => $items,
        ]);
    }
    */
}

    