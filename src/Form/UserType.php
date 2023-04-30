<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Dienstplan;
use App\Entity\Objekt;
use App\Entity\User;
use App\Repository\DienstplanRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserType extends AbstractType
{
    private $doctrine;
    private $tokenStorage;
    public function __construct(TokenStorageInterface $tokenStorage,ManagerRegistry $doctrine)
    {
        $this->tokenStorage = $tokenStorage;
        $this->doctrine = $doctrine;
     }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->tokenStorage->getToken()->getUser();
        //companys des benutzers ermitteln -admin einer Company
        $companies = $this->doctrine->getRepository(Company::class)->findBy(['onjekt_admin' => $user]);
        $count =count($companies);
        if($count>0){
            foreach ($companies as $company) {
                $choices_companies[$company->getName()] = $company;
            }
        }
        else{
            $company = $this->doctrine->getRepository(User::class)->find($user)->getCompany();
            $choices_companies[$company->getName()] = $company;
            }

        $objekts = $this->doctrine->getRepository(Objekt::class)->findBy(['company' => $user->getCompany()]);
        
        $choices_objekt = [];
        foreach ($objekts as $objekt) {
            $choices_objekt[$objekt->getName()] = $objekt;
        }
       

        $builder
            ->add('email')
            ->add('vorname')
            ->add('nachname')
            ->add('adresse')
            ->add('strasse')
            ->add('plz')
            ->add('ort')
            ->add('land')
            ->add('telefon')
            ->add('Steuernummer')
            ->add('Rentenversicherungsnummer')
            ->add('IBAN')
            ->add('Krankenkasse')
            ->add('objekt', ChoiceType::class, [
                'choices' => $choices_objekt,
                'expanded' => true,
                'multiple' => false,
                'label' => 'Objekt',
            ])
            ->add('company', ChoiceType::class, [
                'choices' => $choices_companies,
                'expanded' => true,
                'multiple' => false,
                'label' => 'Company',
            ])
           
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
