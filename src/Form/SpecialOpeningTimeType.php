<?php

namespace App\Form;

use App\Entity\SpecialOpeningTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpecialOpeningTimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('day', DateType::class,[
                'label' => 'Tag',
                'widget' => 'single_text',
            ])
            ->add('start', TimeType::class,[
                'label' => 'öffnen',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('end', TimeType::class,[
                'label' => 'schließen',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('close', CheckboxType::class,[
                'label' => 'an diesem Tag ist geschlossen',
                 'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SpecialOpeningTime::class,
        ]);
    }
}
