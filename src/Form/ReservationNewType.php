<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('user')
            ->add('pax')
            ->add('kommen', DateTimeType::class, [
                'label' => 'kommen' ,
                'date_widget' => 'single_text' ,
                'time_widget' => 'single_text',  
                ])
            ->add('gehen', DateTimeType::class, [
                'label' => 'gehen' ,
                'date_widget' => 'single_text' ,
                'time_widget' => 'single_text' , 
                ])
            
            ->add('fon')
            ->add('mail')
            ->add('points')
            ->add('item')
           
            ->add('update',SubmitType::class,[
                'label' => 'Update' ,
                'attr' => [
                    'class' => 'btn btn-info',
                    
                ]
            ])
            ->add('save',SubmitType::class,[
                'label' => 'save' ,
                'attr' => [
                    'class' => 'btn btn-info',
                    'name' => 'save'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
