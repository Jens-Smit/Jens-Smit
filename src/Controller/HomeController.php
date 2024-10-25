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

#[Route('/')]
class HomeController extends AbstractController
{
    /**
     * The above function in PHP creates a form, processes user input, and sends an email with the form
     * data if the form is submitted and valid.
     * 
     * @param Request request The `` parameter in the `index` method of your Symfony controller
     * represents the current HTTP request. It contains information about the request such as the
     * request method, headers, parameters, and more. In your code snippet, you are using the
     * `` parameter to handle the form submission.
     * @param MailerInterface mailer The code you provided is a Symfony controller action that handles
     * a form submission for sending an email using the MailerInterface service. The MailerInterface
     * service is used to send emails in Symfony applications.
     * 
     * @return Response The code snippet provided is a Symfony controller method for handling a form
     * submission on the homepage route. The method creates a form with fields for email, company name,
     * and message, and a submit button. If the form is submitted and valid, it extracts the form data,
     * constructs an email message, and sends it using the MailerInterface.
     */
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
    /**
     * The above function is a PHP route that renders a Twig template for the "about" page.
     * 
     * @return Response The `about()` function is returning a response that renders the
     * 'Datenschutz.html.twig' template located in the 'home' directory.
     */
    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('home/Datenschutz.html.twig');
    }
    /**
     * The function `contact` in PHP creates a form for users to submit their email and message, sends
     * an email with the submitted data to a specified address, and renders a contact form template.
     * 
     * @param Request request The `` parameter in the `contact` function represents the
     * incoming request made to the `/contact` route. It contains information about the request such as
     * headers, parameters, and other data sent by the client.
     * @param MailerInterface mailer The `` parameter in the `contact` function is an instance
     * of `Symfony\Component\Mailer\Mailer\MailerInterface`. This interface provides methods for
     * sending emails in Symfony applications. In the provided code snippet, the `` service is
     * used to send an email with the user's input data
     * 
     * @return Response The `contact` method is returning a Response object which renders the
     * `contact.html.twig` template with the form data passed to it.
     */
    #[Route('/contact', name: 'contact')]
    public function contact(Request $request,MailerInterface $mailer): Response
    {
        $form = $this->createFormBuilder(null, [
            'attr' => ['class' => 'w-100']
        ])
        ->add('email', EmailType::class, [
            'attr' => ['class' => 'w-100']
        ])
        ->add('Nachricht', TextareaType::class, [
            'attr' => ['class' => 'w-100']
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
          
           $Nachricht = $data['Nachricht'];
           $email = (new Email())
            ->from($user_email)
            ->to('info@tex-mex.de')
            ->subject('Anfrage userForm Gamasy ' )
            ->text($Nachricht)
            ->html('txt');

            $mailer->send($email);
         
       }
        return $this->render('home/contact.html.twig', [
            'form' => $form->createView()
        ]);
    }
   
  /**
   * The above function in PHP is a controller method that renders a Twig template for the "features"
   * route.
   * 
   * @return Response The `features()` method is returning a Response object which renders the
   * 'features.html.twig' template.
   */
    #[Route('/features ', name: 'features')]
    public function features(): Response
    {
        return $this->render('home/features.html.twig');
    }
   /**
    * The above function in PHP defines a route for a privacy page that renders a Twig template for
    * displaying privacy information.
    * 
    * @return Response The `privacy()` method is returning a Response object that renders the
    * `datenschutz.html.twig` template file located in the `home` directory.
    */
    #[Route('/privacy ', name: 'privacy')]
    public function privacy(): Response
    {
        return $this->render('home/datenschutz.html.twig');
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

    