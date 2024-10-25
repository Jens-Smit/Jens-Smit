<?php
/* The code snippet you provided at the beginning of the PHP file is setting up the necessary
namespaces and imports for the `CompanyController` class in a Symfony application. Let's break down
what each line is doing: */

namespace App\Controller;

use App\Entity\Company;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/* The `CompanyController` class in PHP manages company-related operations such as displaying,
creating, editing, and deleting company information based on user roles. */
#[Route('/company')]
/* The CompanyController class in PHP defines methods for managing companies, including displaying,
creating, editing, and deleting company information based on user roles. */
class CompanyController extends AbstractController
{
  /**
   * This PHP function retrieves and displays a list of companies based on the current user's role as
   * an admin or member.
   * 
   * @param CompanyRepository companyRepository The `companyRepository` parameter in the `index` method
   * is an instance of the `CompanyRepository` class. It is used to interact with the database table
   * storing company entities. In this method, it is being used to fetch companies based on certain
   * criteria.
   * @param AuthenticationUtils authenticationUtils The `AuthenticationUtils` service in Symfony
   * provides utility functions related to authentication, such as retrieving the last authentication
   * error and the username of the last authenticated user. In your code snippet, you are injecting the
   * `AuthenticationUtils` service into your controller method.
   * @param UserRepository userRepository The code snippet you provided is a PHP function that seems to
   * be handling the index page for a company-related feature in a web application. It retrieves the
   * current user, checks if the user is an admin of any companies, and then renders the appropriate
   * view based on the user's role.
   * 
   * @return Response If the `` variable is not null and contains company objects, the
   * function will return a rendered template 'company/index.html.twig' with the companies passed as a
   * parameter. If the `` variable is null, the function will redirect to the route
   * 'app_company_new'.
   */
    #[Route('/', name: 'app_company_index', methods: ['GET'])]
    public function index(CompanyRepository $companyRepository, AuthenticationUtils $authenticationUtils, UserRepository $userRepository): Response
    {
        //aktuelle benutzer
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        //companys des benutzers ermitteln -admin einer Company
        $companys = $companyRepository->findBy((['onjekt_admin' => $user ]));
        $count = count($companys);
        if($count < 1){ //wenn kein admin einer Company nutze die Company aus User
            $companys  = $this->container->get('security.token_storage')->getToken()->getUser()->getCompany();
            if($companys == Null){}else{
                $companys = ["0"=> $companys];
            }
        }
        if($companys == Null){
            
            return $this->redirectToRoute('app_company_new');
            
        }else{
            return $this->render('company/index.html.twig', [
                'companies' =>  $companys,
            ]);
        }
    }

   /**
    * This PHP function creates a new Company entity, associates it with the current user, and saves it
    * to the database if the form data is valid.
    * 
    * @param Request request The `` parameter in the `new` method is an instance of the
    * Symfony\Component\HttpFoundation\Request class. It represents the current HTTP request and
    * contains information such as GET and POST parameters, headers, and more.
    * @param UserRepository userRepository The `UserRepository` in the code snippet is responsible for
    * handling database operations related to user entities. It likely contains methods for saving user
    * entities to the database, updating user information, and retrieving user data.
    * @param CompanyRepository companyRepository The `companyRepository` parameter in the `new` method
    * is an instance of `CompanyRepository` class. It is used to interact with the database and perform
    * operations related to the `Company` entity, such as saving a new company to the database.
    * 
    * @return Response If the form is submitted and valid, the method will return a redirection to the
    * 'app_objekt_index' route with a HTTP status code of 303 (See Other). If the form is not submitted
    * or not valid, the method will return a rendered form template 'company/new.html.twig' with the
    * company and form variables passed to it.
    */
    #[Route('/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository, CompanyRepository $companyRepository): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        
        $company->setOnjektAdmin($user);


        if ($form->isSubmitted() && $form->isValid()) {
            $companyRepository->save($company, true);

            $user->setCompany($company);
            $userRepository->save($user, true);

            
            return $this->redirectToRoute('app_objekt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('company/new.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    /**
     * This PHP function checks if the current user is an admin of a company and displays the company
     * details if they are, otherwise it shows a message indicating no roles.
     * 
     * @param Company company The code snippet you provided is a PHP function that handles the logic
     * for displaying a company based on the user's role. Let me explain the logic step by step:
     * @param CompanyRepository companyRepository The `companyRepository` parameter in the `show`
     * method is an instance of the `CompanyRepository` class. It is used to interact with the database
     * and perform operations related to the `Company` entity, such as fetching company data from the
     * database.
     * 
     * @return Response If the `` object is found in the `` array, the method will
     * return a rendered template `company/show.html.twig` with the `company` variable passed to it.
     * Otherwise, if the `` object is not found in the `` array, the method will
     * return a rendered template `dashboard/noroles.html.twig` without passing any variables to it.
     */
    #[Route('/{id}', name: 'app_company_show', methods: ['GET'])]
    public function show(Company $company, CompanyRepository $companyRepository): Response
    {
         //aktuelle benutzer
         $user = $this->container->get('security.token_storage')->getToken()->getUser();
         //companys des benutzers ermitteln -admin einer Company
         $companys = $companyRepository->findBy((['onjekt_admin' => $user ]));
         $count = count($companys);
         if($count < 1){ //wenn kein admin einer Company nutze die Company aus User
             $companys  = $this->container->get('security.token_storage')->getToken()->getUser()->getCompany();
         }
         if(in_array($company, $companys)){
            return $this->render('company/show.html.twig', [
                'company' => $company,
            ]);
        }else{
            return $this->render('dashboard/noroles.html.twig', [
                
            ]);
        }
    }

   

    /**
     * This PHP function handles the editing of a Company entity, including form submission and
     * validation, and redirects to a specified route upon successful submission.
     * 
     * @param Request request The `` parameter in the `edit` method is an instance of the
     * Symfony\Component\HttpFoundation\Request class. It represents the current HTTP request and
     * contains information such as the request method, headers, parameters, and more.
     * @param Company company The `edit` method in the code snippet is a controller action that handles
     * the editing of a Company entity. The parameters passed to this method are:
     * @param CompanyRepository companyRepository The `` parameter in the `edit`
     * method is an instance of the `CompanyRepository` class. It is used to interact with the database
     * and perform operations related to the `Company` entity, such as saving or retrieving company
     * data.
     * 
     * @return Response If the form is submitted and valid, the function will return a redirection
     * response to the route named 'app_objekt_index' with an empty array of parameters and a status
     * code of 303 (HTTP_SEE_OTHER). If the form is not submitted or not valid, the function will
     * return a rendered form template 'company/edit.html.twig' with the company entity and the form
     * object as variables.
     */
    #[Route('/{id}/edit', name: 'app_company_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Company $company, CompanyRepository $companyRepository): Response
    {
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $companyRepository->save($company, true);

            return $this->redirectToRoute('app_objekt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('company/edit.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

  /**
   * This PHP function deletes a company entity based on the provided ID after validating the CSRF
   * token.
   * 
   * @param Request request The `` parameter in the `delete` method is an instance of the
   * Symfony\Component\HttpFoundation\Request class. It represents the current HTTP request and
   * contains information such as the request method, headers, parameters, and more.
   * @param Company company The `company` parameter in the `delete` method is an instance of the
   * `Company` entity class. It is being used to represent the specific company entity that is being
   * targeted for deletion in this controller action. The `Company` entity is likely a representation
   * of a company record in the database,
   * @param CompanyRepository companyRepository The `` parameter in the `delete`
   * method is an instance of the `CompanyRepository` class. It is being used to interact with the
   * database and perform operations related to the `Company` entity, such as removing a company from
   * the database using the `remove` method.
   * 
   * @return Response The `delete` method is returning a response that redirects to the
   * `app_objekt_index` route with an empty array of parameters and a status code of `303 See Other`.
   */
    #[Route('/{id}', name: 'app_company_delete', methods: ['POST'])]
    public function delete(Request $request, Company $company, CompanyRepository $companyRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$company->getId(), $request->request->get('_token'))) {
            $companyRepository->remove($company, true);
        }

        return $this->redirectToRoute('app_objekt_index', [], Response::HTTP_SEE_OTHER);
    }
}
