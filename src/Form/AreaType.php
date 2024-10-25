<?php
namespace App\Form;

use App\Entity\Area;
use App\Entity\Company;
use App\Entity\Objekt;
use App\Entity\User;
use App\Repository\ObjektRepository;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\DBAL\Types\TextType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AreaType extends AbstractType
{ 
    private $doctrine;
    private $tokenStorage;
    public function __construct( TokenStorageInterface $tokenStorage,ManagerRegistry $doctrine)
    {
        
        $this->tokenStorage = $tokenStorage;
        $this->doctrine = $doctrine;
     }
    public function buildForm( FormBuilderInterface $builder, array $options): void
    {
        
        $objektRepository = $this->doctrine->getRepository(Objekt::class);
        $user = $this->tokenStorage->getToken()->getUser();
        
        $objekts = $objektRepository->findBy(['company'=> $user->getCompany()]);
      
        $choices_objekte = [];
        foreach ($objekts as $objekt){
        $choices_objekte[$objekt->getName()] = $objekt;
        }
     // dump($choices_objekte);

    $builder
       
        ->add('name', null,[
            'label' => 'Bezeichnung' ,
        ])
        ->add('map', FileType::class,array(
            'data_class' => null,
            'label' => 'Karte' ,

        ))
        ->add('objekt', ChoiceType::class, [
            'choices' => $choices_objekte,
            'choice_label' => 'name',
            'label' => 'Standort' ,
            'choice_value' => 'id',
        ])
        
        ->add('save',SubmitType::class,[
            'label' => 'speichern' ,
            'attr' => [
            'class' => 'btn btn-primary w-100',
            'name' => 'Speichern'
            ]
            ])
            
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Area::class,
        ]);
    }
}
