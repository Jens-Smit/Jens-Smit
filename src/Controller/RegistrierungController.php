<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrierungController extends AbstractController
{
    #[Route('/Registrierung', name: 'reg')]
    public function reg(Request $request,UserPasswordHasherInterface $passEncoder,ManagerRegistry $doctrine): Response
    {
        $regform = $this->createFormBuilder()
        ->add('email', TextType::class,[
            'label' => 'Email'])
        ->add('vorname', TextType::class,[
            'label' => 'Vorname'])
        ->add('nachname', TextType::class,[
            'label' => 'Nachname'])
        ->add('password', RepeatedType::class,[
            'type' => PasswordType::class,
            'required' => true,
            'first_options' =>['label'=> 'Passwort'],
            'second_options' =>['label'=> 'Passwort wiederholden']
        ])
        ->add('registrieren', SubmitType::class)
        ->getForm()
        ;
        $regform -> handleRequest($request);
        if($regform->isSubmitted()){
                $eingabe = $regform->getData();
                $user = new User();
                $user->setEmail($eingabe['email']);
                $user->setVorname($eingabe['vorname']);   
                $user->setNachname($eingabe['nachname']);              
                $user->setPassword(
                    $passEncoder->hashPassword($user, $eingabe['password'])
                );
                $em=$doctrine->getManager();
                $em->persist($user);
                $em->flush();
                return $this->redirect($this->generateUrl('home'));
        }
        return $this->render('registrierung/index.html.twig', [
           'regform' => $regform->createView()
        ]);
    }
}
