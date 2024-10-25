<?php

namespace App\Form;

use App\Entity\ContractData;
use App\Entity\User;
use App\Entity\CompensationTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bezeichnung',TextType::class,[
                'label'=> 'Bezeichnung der Vertragsdaten',
            ])
            ->add('startDate', DateType::class, [
                'data' => new \DateTime(),
                'label'=> 'Tage des Vertragsstarts',
            ])
            ->add('singDate', DateType::class, [
                'data' => new \DateTime(),
                'label'=> 'Tag der Vnterschrift',
            ])
            ->add('lohn',NumberType::class,[
                'label'=> 'Vergütung in euro',
            ])
            ->add('stunden',NumberType::class,[
                'label'=> 'wöchentliche Arbeitsstunden',
            ])
            ->add('arbeitstage',NumberType::class,[
                'label'=> 'wöchentliche Arbeitstage',
            ])
            ->add('endDate',DateType::class,[
                'label'=> 'Austrittsdatum',
                'required' => false,
                'empty_data' => null,
            ])
            ->add('Urlaub',NumberType::class,[
                'label'=> 'Jahresurlaub in Tagen',
            ])
            
            ->add('user',EntityType::class,[ 
                'class' => User::class,
                'label'=> false,
                 'attr' => [
                    'style' => 'display:none;'
                    ] 
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
