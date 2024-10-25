<?php

namespace App\Controller;

use App\Entity\Vertrag;
use App\Entity\Objekt;
use App\Entity\VertragVariable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

#[Route('/vertrag')]
class VertragController extends AbstractController
{
    #[Route('/', name: 'app_vertrag', methods: ['GET', 'POST'])]
    public function index(ManagerRegistry $doctrine, Request $request,): Response
    { 
        $objektId = $request->request->get('objektId');
       
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $objekts = $doctrine->getRepository(Objekt::class)->findBy(['company' => $user->getCompany()]);

        $vertraege = [];
        foreach ($objekts as $objekt) {
            if($objekt->getId() == $objektId){
                $vertraege = array_merge($vertraege, $objekt->getVertrags()->toArray());
            }
            
        }
        
       /*
        $objekt = $doctrine->getRepository(Objekt::class)->find($date);
       $vertraege =  $objekt->getVertrags()->toArray(); */
        return $this->render('vertrag/index.html.twig', [
            'vertraege' => $vertraege,
        ]);
    }
    #[Route('/new', name: 'app_vertrag_new')]
    public function newVertrag(Request $request,ManagerRegistry $doctrine): Response
    { 
       
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $variablen = $doctrine->getRepository(VertragVariable::class)->findAll();
        $objekts = $doctrine->getRepository(Objekt::class)->findBy(['company' => $user->getCompany()]);
        
        $choices_objekt = [];
        foreach ($objekts as $objekt) {
            $choices_objekt[$objekt->getName()] = $objekt;
        }

        $vertrag = new Vertrag();
        $form = $this->createFormBuilder($vertrag)
            ->add('titel', TextType::class)
            ->add('discription', TextType::class)
            ->add('objekt', ChoiceType::class, [
                'choices' => $choices_objekt,
                'expanded' => true,
                'multiple' => false,
                'label' => 'Objekt',
            ])
            ->add('text', CKEditorType::class, [
                'attr' => [
                    'id' => 'form_textarea',
                    'name' => 'editor',
                ]
            ])
            ->getForm();
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($vertrag);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_vertrag_show', ['id' => $vertrag->getId()]);
        }
    
        return $this->render('vertrag/new.html.twig', [
            'form' => $form->createView(),
            'variablen' => $variablen,
        ]);
    }
    #[Route('/show/{id}', name: 'app_vertrag_show', methods: ['GET'])]
    public function vertrag_show(Vertrag $vertrag): Response
    {
        return $this->render('vertrag/show.html.twig', [
            'vertrag' => $vertrag,
        ]);
    }
    #[Route('/edit/{id}', name: 'app_vertrag_edit')]
    public function editVertrag(Request $request, Vertrag $vertrag, ManagerRegistry $doctrine): Response
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $objekts = $doctrine->getRepository(Objekt::class)->findBy(['company' => $user->getCompany()]);
        $variablen = $doctrine->getRepository(VertragVariable::class)->findAll();
       
        $choices_objekt = [];
        foreach ($objekts as $objekt) {
            $choices_objekt[$objekt->getName()] = $objekt;
        }

        $form = $this->createFormBuilder($vertrag)
            ->add('titel', TextType::class)
            ->add('discription', TextareaType::class)
            ->add('objekt', ChoiceType::class, [
                'choices' => $choices_objekt,
                'expanded' => true,
                'multiple' => false,
                'label' => 'Objekt',

            ])
            ->add('text', CKEditorType::class, [
                'attr' => [
                    'id' => 'form_textarea',
                    'name' => 'editor',
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('app_vertrag_show', ['id' => $vertrag->getId()]);
        }

        return $this->render('vertrag/edit.html.twig', [
            'form' => $form->createView(),
            'variablen' => $variablen,
        ]);
    }
}
