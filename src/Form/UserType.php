<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Objekt;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/* The `UserType` class in PHP defines a form builder method to create a form with fields for user
information, including email, name, birthday, address details, contact information, and choices for
object and company selection based on user permissions. */
class UserType extends AbstractType
{
   /**
    * The constructor initializes the TokenStorageInterface and ManagerRegistry objects.
    * 
    * @param TokenStorageInterface tokenStorage The `TokenStorageInterface` is a part of Symfony's
    * security component and is used for storing the security token of the currently authenticated
    * user. It allows you to access the current user's information, such as roles and username, during
    * the request.
    * @param ManagerRegistry doctrine The `` parameter in the constructor is of type
    * `ManagerRegistry`. This is typically used in Symfony applications for managing database
    * connections and performing database operations using Doctrine ORM. It provides access to entity
    * managers and repositories for interacting with the database.
    */
    private $doctrine;
    private $tokenStorage;
    /**
     * The function is a PHP constructor that initializes the TokenStorageInterface and ManagerRegistry
     * objects.
     * 
     * @param TokenStorageInterface tokenStorage The `TokenStorageInterface` parameter typically refers
     * to a service that stores the security token of the currently authenticated user in Symfony
     * applications. It allows you to access the current user's information, such as roles and
     * username, during the request lifecycle.
     * @param ManagerRegistry doctrine The `doctrine` parameter in the constructor is of type
     * `ManagerRegistry`. This is typically used in Symfony applications for managing database
     * connections and interacting with the database through Doctrine ORM. The `ManagerRegistry`
     * provides access to entity managers and repositories, allowing you to perform database operations
     * within your application.
     */
    public function __construct(TokenStorageInterface $tokenStorage,ManagerRegistry $doctrine)
    {
        $this->tokenStorage = $tokenStorage;
        $this->doctrine = $doctrine;
     }

 /**
  * The `buildForm` function in PHP builds a form with fields for user information, including email,
  * name, birthday, address details, contact information, and choices for object and company selection
  * based on user permissions.
  * 
  * @param FormBuilderInterface builder The `builder` parameter in the `buildForm` method is an
  * instance of `FormBuilderInterface` which is used to define the structure of the form. It allows you
  * to add form fields and configure their options. In your code snippet, you are using the `builder`
  * to add various form fields
  * @param array options The `buildForm` function you provided is a Symfony form builder method used to
  * build a form with various form fields. Let me explain the parameters and the purpose of this
  * function:
  */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->tokenStorage->getToken()->getUser();
        //companys des benutzers ermitteln -admin einer Company
        $companies = $this->doctrine->getRepository(Company::class)->findBy(['onjekt_admin' => $user]);
        $count =count($companies);
        $company = null;
        if($count>0){
            foreach ($companies as $company) {
                $choices_companies[$company->getName()] = $company;
            }
        }
        else{
            $company = $this->doctrine->getRepository(User::class)->find($user)->getCompany();
            $choices_companies[$company->getName()] = $company;
            }

        $objekts = $this->doctrine->getRepository(Objekt::class)->findBy(['company' => $user->getCompany()]);
        
        $choices_objekt = [];
        foreach ($objekts as $objekt) {
            $choices_objekt[$objekt->getName()] = $objekt;
        }
       $sign = $company->getSign();

        $builder
            ->add('email', TextType::class,[
                
            ])
           
            ->add('vorname')
            ->add('nachname')
            ->add('birthday', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'years' => range(1900, date('Y')),
            ])
            ->add('adresse')
            ->add('strasse')
            ->add('plz')
            ->add('ort')
            ->add('land')
            ->add('telefon')
            ->add('Steuernummer')
            ->add('Rentenversicherungsnummer')
            ->add('IBAN')
            ->add('Krankenkasse')
            ->add('objekt', ChoiceType::class, [
                'choices' => $choices_objekt,
                'expanded' => true,
                'multiple' => false,
                'label' => 'Objekt',
                'required' => false,
            ])
            ->add('company', ChoiceType::class, [
                'choices' => $choices_companies,
                'expanded' => true,
                'multiple' => false,
                
                'label' => 'Company',
            ])
           
        ;
        
    }

    /**
     * The function `configureOptions` sets default options for a form in PHP.
     * 
     * @param OptionsResolver resolver The `resolver` parameter in the `configureOptions` function is
     * an instance of the `OptionsResolver` class. It is used to configure the options for a form type
     * in Symfony. In this specific code snippet, the `setDefaults` method is being called on the
     * `` object to set the
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
