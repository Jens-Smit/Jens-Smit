<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Dienstplan;
use App\Entity\Objekt;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DienstplanType extends AbstractType
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
         
        foreach ($companies as $company) {
            $objekts = $this->doctrine->getRepository(Objekt::class)->findBy(['company' => $company->getId()]);
            foreach ($objekts as $objekt) {
            $choices[$objekt->getName()] = $objekt;
            }
        }
        $builder
            ->add('bezeichnung')
            ->add('start')
            ->add('ende')
            ->add('Objket', ChoiceType::class, [
                'choices' => $choices,
                'expanded' => true,
                'multiple' => false,
                'label' => 'Objekt',
            ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dienstplan::class,
        ]);
    }
}
