<?php

namespace App\Form;

use App\Entity\ItemCategories;
use App\Entity\ItemCategoriesPrice;
use App\Entity\RentItems;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemCategoriesType extends AbstractType
{
   
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $itemCategoriesPrices = $builder->getData()->getItemCategoriesPrices();
        $Rentitem = $builder->getData()->getRentItems();
        $builder
        ->add('name',TextType::class,[
            'label' => 'Bezeichnung',
        ])
        ->add('booktime', NumberType::class,[
            'label' => 'Aufenthaltsdauer',
        ])
        ->add('objekt')
        ->add('rentItems', EntityType::class, [
            
            'class' => RentItems::class,
            'choices' => $Rentitem,
            'multiple' => true,
            'disabled' => true, 
            'required' =>false,// Korrekte Schreibweise
            // ... andere Optionen
        ])
        ->add('itemCategoriesPrices', EntityType::class, [
            'class' => ItemCategoriesPrice::class,
            'choices' => $itemCategoriesPrices,
            'multiple' => true, 
            'disabled' => true, 
            'required' =>false,// Korrekte Schreibweise
            // ... andere Optionen
        ]);
     }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ItemCategories::class,
        ]);
    }
}
