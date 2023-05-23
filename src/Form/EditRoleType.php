<?php

namespace App\Form;

use App\Entity\Roles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EditRoleType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $roles = $this->entityManager
                ->getRepository(Roles::class)
                ->findAll();
        

            foreach ($roles as $role) {
                $choices[$role->getName()] = $role->getName();
            }
            
            
            $builder
                ->setMethod('POST')
                ->add('roles', ChoiceType::class, [
                    'choices' => $choices,
                    'expanded' => true,
                    'multiple' => true,
                    'label' => 'Roles',
                ])
                ->add('submit', SubmitType::class, [
                    'label' => 'Save',
                    'attr' => ['class' => 'btn btn-info w-100'],
                ]);
    }
}
