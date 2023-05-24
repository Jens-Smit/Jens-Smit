<?php

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

#[Route('/company')]
class CompanyController extends AbstractController
{
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

    #[Route('/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CompanyRepository $companyRepository): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
      
        $company->setOnjektAdmin($user);


        if ($form->isSubmitted() && $form->isValid()) {
            $companyRepository->save($company, true);

            return $this->redirectToRoute('app_objekt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('company/new.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

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

    #[Route('/{id}', name: 'app_company_delete', methods: ['POST'])]
    public function delete(Request $request, Company $company, CompanyRepository $companyRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$company->getId(), $request->request->get('_token'))) {
            $companyRepository->remove($company, true);
        }

        return $this->redirectToRoute('app_objekt_index', [], Response::HTTP_SEE_OTHER);
    }
}
