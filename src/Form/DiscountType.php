<?php

namespace App\Form;

use App\Entity\Discount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titel')
            ->add('value')
            ->add('conditions', ChoiceType::class, [
                'choices' => [
                    'Frühbucher' => 'earlybooking',
                    'lastminut' => 'lastminut',
                    'sofortzahlen' => 'earlypay',
                ],
                'multiple' => false, // Set to true if you want to allow multiple selections
                'expanded' => false, // Set to true for radio buttons, false for a select dropdown
            ])
            ->add('itemCategoriePrice')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Discount::class,
        ]);
    }
}
