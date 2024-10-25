<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Objekt;
use App\Entity\ObjektCategories;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ObjektType extends AbstractType

{   private $doctrine;
    private $tokenStorage;
    public function __construct(TokenStorageInterface $tokenStorage,ManagerRegistry $doctrine)
    {
        $this->tokenStorage = $tokenStorage;
        $this->doctrine = $doctrine;
    }

    public function buildForm( FormBuilderInterface $builder, array $options): void
    {
        $user = $this->tokenStorage->getToken()->getUser();
        //companys des benutzers ermitteln -admin einer Company
        $companies = $this->doctrine->getRepository(Company::class)->findBy(['onjekt_admin' => $user]);

        $userobjekt = $this->doctrine->getRepository(User::class)->find( $user); 
        $objekt = $userobjekt->getObjekt();
        foreach ($companies as $company) {
            $choices[$company->getName()] = $company;
        }
        
    
        $builder
            ->add('name',null,[
                'label' => 'Bezeichnung *',
            ])
            ->add('adresse',null,[
                'label' => 'Straße & Hausnummer *',
            ])
            ->add('ort',null,[
                'label' => 'Ort *',
            ])
           
            ->add('plz',null,[
                'label' => 'Postleitzahl *',
            ])
            ->add('main_mail',null,[
                'label' => 'Emailadresse *',
            ])
            
            ->add('telefon',null,[
                'label' => 'Telefonnummer *',
            ])
            ->add('website', null, [
                'required' => false,
            ]);
            
            if($objekt and $objekt->getBild()){
                $builder->add('bild', FileType::class,array(
                    'data_class' => null,
                     'required' => false,
                     'label' => 'Objektbild',
                   'data' => $objekt->getBild(), // Setze den Wert des Feldes auf den Namen des vorhandenen Bildes
                ));
            }
            else{
                $builder->add('bild', FileType::class,array(
                    'data_class' => null,
                     'required' => false,
                     'label' => 'Objektbild',
                     
    
                    
                ));
            }
            $builder->add('fax', null, [
                'required' => false,
                'label' => 'Faxnummer',
            ])
            ->add('bestellung_mail', null, [
                'required' => false,
                'label' => 'Mail Warenwirtschaft',
            ])
            ->add('fibi_mail', null, [
                'required' => false,
                'label' => 'Mail Finanzbuchhaltung',
            ])
            ->add('ust_id', null, [
                'required' => false,
                'label' => 'Ust ID',
            ])
            ->add('Handelsregister', null, [
                'required' => false,
            ])
            ->add('Amtsgericht', null, [
                'required' => false,
            ])
            ->add('company', ChoiceType::class, [
                'choices' => $choices,
                'expanded' => true,
                'multiple' => false,
                'label' => 'Unternehmen  *',
            ])
            ->add('categories', EntityType::class, [
                'class' => ObjektCategories::class,
                'label' => 'Kategorie auswählen  *',
            ])
        ;
    }
    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Objekt::class,
        ]);
    }
}
