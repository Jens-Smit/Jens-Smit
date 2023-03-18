<?php

namespace App\Form;

use App\Entity\ContractData;
use App\Entity\User;
use App\Entity\CompensationTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class)
            ->add('singDate', DateType::class)
            ->add('lohn')
            ->add('stunden')
            ->add('endDate',DateType::class)
            ->add('Urlaub')
            ->add('bezeichnung')
            ->add('user',EntityType::class,[
                'class' => User::class,
            ])
            ->add('CompensationTypes', EntityType::class, [
                'class' => CompensationTypes::class,
                'choice_label' => 'name', // Change 'name' to the property you want to display
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContractData::class,
        ]);
    }
}
