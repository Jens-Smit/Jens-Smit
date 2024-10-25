<?php

namespace App\Form;

use App\Entity\RentItems;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RentItemsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('id', NumberType::class, [
            'required' => false,  
        ])
            ->add('name')
            ->add('description')
            ->add('pax')
            ->add('objekt')
            ->add('Category')
            ->add('area')
            ->add('status',CheckboxType::class,[
                'label' => 'Aktiv' ,
            ])
            ->add('save',SubmitType::class,[
                'label' => 'save' ,
                'attr' => [
                    'class' => 'btn btn-info w-100',
                    'name' => 'save'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RentItems::class,
        ]);
    }
}
