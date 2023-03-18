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
    public function __construct(ObjektRepository $objektRepository ,TokenStorageInterface $tokenStorage,ManagerRegistry $doctrine)
    {
        $this->objektRepository = $objektRepository;
        $this->tokenStorage = $tokenStorage;
        $this->doctrine = $doctrine;
     }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $objektRepository = $this->doctrine->getRepository(Objekt::class);
        $user = $this->tokenStorage->getToken()->getUser();
        $objekte = $objektRepository->findMy($user);
//dump($objekte);
    $builder
       
        ->add('name')
        ->add('map', FileType::class,array(
            'data_class' => null,

        ))
        ->add('objekt', ChoiceType::class, [
            'choices' => $objekte,
            'choice_label' => 'name',
            'choice_value' => 'id',
        ])
        ->add('save',SubmitType::class,[
            'label' => 'save' ,
            'attr' => [
            'class' => 'btn btn-success w-100',
            'name' => 'save'
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
