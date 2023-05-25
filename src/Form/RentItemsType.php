<?php

namespace App\Form;

use App\Entity\Area;
use App\Entity\Company;
use App\Entity\Objekt;
use App\Entity\RentItems;
use App\Repository\ObjektRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Intl\Scripts;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RentItemsType extends AbstractType
{
    private $doctrine;
    private $tokenStorage;
    public function __construct(TokenStorageInterface $tokenStorage,ManagerRegistry $doctrine)
    {
        $this->tokenStorage = $tokenStorage;
        $this->doctrine = $doctrine;
     }

     public function buildForm(FormBuilderInterface $builder, array $options): void
     {
         $user = $this->tokenStorage->getToken()->getUser();
         
         // Companys des Benutzers ermitteln - Admin einer Company
         $objekts = $this->doctrine->getRepository(Objekt::class)->findBy(['company' => $user->getCompany()]);
         $choices_objekt = [];
         foreach ($objekts as $objekt) {
             $choices_objekt[$objekt->getName()] = $objekt;
         }
         
         $builder
             ->add('id', NumberType::class, [
                 'required' => false,  
             ])
             ->add('name')
             ->add('description')
             ->add('pax')
             ->add('Category')
             ->add('objekt', ChoiceType::class, [
                 'choices' => $choices_objekt,
                 'expanded' => true,
                 'multiple' => false,
                 'label' => 'objekt',
                 'attr' => [
                     'class' => 'objekt-select', // Füge eine Klasse für das Objekt-Auswahlfeld hinzu
                 ],
             ]);
 
         // Bereichsauswahl aktualisieren
         $builder->addEventListener(
             FormEvents::PRE_SET_DATA, 
             function (FormEvent $event) {
                $data = $event->getData();
                dump($data);
                $areas = $this->doctrine->getRepository(Area::class)->findBy(['objekt' => $data'objekt']]);
                $choices_areas = [];
                foreach ($areas as $area) {
                    $choices_areas[$area->getName()] = $area;
                }
                 $form = $event->getForm();
                 $form ->add('area', ChoiceType::class, [
                    'choices' => $choices_areas,
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Area',
                    'attr' => [
                        'class' => 'area-select',
                        'disabled' => 'disabled',
                    ],
                ]);
                 
                 
             }
         );
         
         $builder->add('status')
             ->add('save', SubmitType::class, [
                 'label' => 'save',
                 'attr' => [
                     'class' => 'btn btn-info w-100',
                     'name' => 'save',
                 ],
             ]);
     }
     

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RentItems::class,
        ]);

    }
}

