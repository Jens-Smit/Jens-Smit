<?php

namespace App\Form;

use App\Entity\OpeningTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpeningTimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('day', ChoiceType::class, [
                'label' => 'day',
                'choices'  => [
                    'Sonntag' => 0,
                    'Montag' => 1,
                    'Dienstag' => 2,
                    'Mittwoch' => 3,
                    'Donnerstag' => 4,
                    'Freitag' => 5,
                    'Sammstag' => 6,
                ],
                'attr' =>[
                'style' =>'padding:5px; width:100%;'
                ],
            ])
            ->add('start', TimeType::class,[
                'label' => 'open',
                'widget' => 'single_text',
            ])
            ->add('end', TimeType::class,[
                'label' => 'close',
                'widget' => 'single_text',
            ])
            ->add('effective_date', DateType::class,[
                'label' => 'day',
                'widget' => 'single_text',
                
            ])
            ->add('objekt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OpeningTime::class,
        ]);
    }
}
