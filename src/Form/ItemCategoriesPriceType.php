<?php

namespace App\Form;

use App\Entity\ItemCategories;
use App\Entity\ItemCategoriesPrice;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType ;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ItemCategoriesPriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start', DateType::class)
            ->add('end', DateType::class)
            ->add('price', null, array(
                'label' => false,
            ))
            ->add('ItemCategory', EntityType::class, [
                'class' => ItemCategories::class,
                'choice_label' => 'name', // Der Name des Feldes, das in der Dropdown-Liste angezeigt wird
               // 'multiple' => true, // Erlaubt die Auswahl mehrerer Kategorienpreise
                'expanded' => true, // Zeigt die Kategorienpreise als Checkboxen an
            ])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ItemCategoriesPrice::class,
        ]);
    }
}
