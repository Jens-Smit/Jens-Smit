<?php

namespace App\Form;

use App\Entity\RentItems;
use App\Entity\Reservation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', NumberType::class,[
                'label'=>false,
                'attr'=>[
                    'style'=>'display:none;'
                ]
            ])
            ->add('user')
            ->add('pax')
            ->add('kommen', DateTimeType::class, [
                'label' => 'kommen' ,
                'date_widget' => 'single_text' ,
                'time_widget' => 'single_text'  ])
            ->add('gehen', DateTimeType::class, [
                'label'=>false,
                'attr'=>[
                    'style'=>'display:none;'
                ]
            ])
            
            ->add('fon')
            ->add('mail')
            ->add('points', NumberType::class,[
                'data'=> 1,
                'label'=>false,
                'required'=>false,
                'attr'=>[
                    'style'=>'display:none;'
            ]])
            ->add('item',EntityType::class,[
                'class'=> RentItems::class,
                'label'=>false,
                'attr'=>[
                    'style'=>'display:none;'
            ]])
           
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
