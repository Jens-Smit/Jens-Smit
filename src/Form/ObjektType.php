<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Objekt;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ObjektType extends AbstractType

{   private $doctrine;
    private $tokenStorage;
    public function __construct(TokenStorageInterface $tokenStorage,ManagerRegistry $doctrine)
    {
        $this->tokenStorage = $tokenStorage;
        $this->doctrine = $doctrine;
    }

    public function buildForm( FormBuilderInterface $builder, array $options): void
    {
        $user = $this->tokenStorage->getToken()->getUser();
        //companys des benutzers ermitteln -admin einer Company
        $companies = $this->doctrine->getRepository(Company::class)->findBy(['onjekt_admin' => $user]);

        foreach ($companies as $company) {
            $choices[$company->getName()] = $company->getName();
        }
        
    
        $builder
            ->add('name')
            ->add('adresse')
            ->add('ort')
            ->add('bild', FileType::class,array(
                'data_class' => null
            ))
            ->add('plz')
            ->add('main_mail')
            ->add('website')
            ->add('telefon')
            ->add('fax')
            ->add('bestellung_mail')
            ->add('fibi_mail')
            ->add('ust_id')
            ->add('Handelsregister')
            ->add('Amtsgericht')
            ->add('company', ChoiceType::class, [
                'choices' => $choices,
                'expanded' => true,
                'multiple' => false,
                'label' => 'company',
            ])
        ;
    }
    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Objekt::class,
        ]);
    }
}
