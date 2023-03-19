<?php

namespace App\Controller;


use TCPDF;
use App\Entity\User;
use App\Entity\UserContrectData;
use App\Entity\UserDokumente;
use App\Entity\Vertrag;
use App\Entity\VertragVariable;
use App\Form\EditRoleType;
use App\Form\UserType;
use App\Form\UserDokumenteType;
use App\Repository\ContractDataRepository;
use App\Repository\UserDokumenteRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\Persistence\ManagerRegistry;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Config\Framework\MailerConfig;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, AuthenticationUtils $authenticationUtils): Response
    {
        //aktuelle benutzer
        // Aktuellen Benutzer abrufen
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if (in_array("ROLE_HR", $user->getRoles())){
            $Company_id= $user->getCompany()->getId();
        
              return $this->render('user/index.html.twig', [
                  'users' => $userRepository->findBy(['company' => $Company_id ]),
                  
              ]);       
        }
        else{
            return $this->render('user/show.html.twig', [
                'user' => $user,
                 ]);
           
         }
    }
    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/password_reset', name: 'password_reset', methods: ['GET', 'POST'])]
    public function NewPassword(Request $request,ManagerRegistry $doctrine,UserPasswordHasherInterface $passEncoder,MailerInterface $mailer): Response
    {

        $form = $this->createFormBuilder()
        ->add('email', EmailType::class)
        ->add('submit', SubmitType::class, ['label' => 'Passwort zurücksetzen'])
        ->getForm();
       $form -> handleRequest($request);
       

       if ($form->isSubmitted() && $form->isValid()) {
           $data = $form->getData();
           $user_email = $data['email'];
           $entityManager = $doctrine->getManager();
           $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $user_email]);
             
       
           if (!$user) {
            $this->addFlash(
                'danger',
                'User nicht gefunden'
            );
           }else{
            $plainPassword = substr(md5(microtime()), 0, 8);
            $encodedPassword = $passEncoder->hashPassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
            $entityManager->persist($user);
            $entityManager->flush();
            $email = (new Email())
            ->from('info@tex-mex.de')
            ->to($user_email)
            ->subject('Ihre Password wurde zurückgesetzt')
            ->text('Ihre Password wurde zurückgesetzt')
            ->html('<p>Ihr Passwort ist <b>'.$plainPassword.'</b></p>');

            $mailer->send($email);
            $this->addFlash(
                'success',
                'Passwort zurückgesetzt'
            );
           }
       }
        return $this->render('user/pw_reset.html.twig', [
           'form' => $form->createView()
        ]);
  
    }
    #[Route('/changePassword', name: 'changePassword', methods: ['GET', 'POST'])]
    public function changePassword(ManagerRegistry $doctrine, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordEncoder)
    {
        $entityManager = $doctrine->getManager();
        // Erstelle ein Formular, um das neue Passwort einzugeben
        $form = $this->createFormBuilder()
            ->add('oldPassword', PasswordType::class, ['label' => 'Aktuelles Passwort'])
            ->add('new_password', PasswordType::class, ['label' => 'Neues Passwort'])
            ->add('confirm_password', PasswordType::class, ['label' => 'Passwort bestätigen'])
            ->add('submit', SubmitType::class, ['label' => 'Passwort ändern'])
            ->getForm();

        $form->handleRequest($request);

        // Wenn das Formular abgeschickt wurde und gültig ist, ändere das Passwort
        if ($form->isSubmitted() && $form->isValid()) {
            // Hole die Daten aus dem Formular
            $data = $form->getData();
            // Hole den aktuellen Benutzer
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
         
            // Verschlüssele das neue Passwort
            if (!$passwordEncoder->isPasswordValid($user, $data['oldPassword'])) {
                // Setze eine Fehlermeldung, falls das alte Passwort nicht korrekt ist
                $this->addFlash('danger', 'Passwort flasch');
            } 
            else {
                $encodedPassword = $passwordEncoder->hashPassword($user, $data['new_password']);
                // Setze das neue, verschlüsselte Passwort für den Benutzer
                
                // Speichere den Benutzer in der Datenbank
                $user->setPassword($encodedPassword);
                $entityManager->persist($user);
                $entityManager->flush();
                

                $this->addFlash('success', 'Passwort erfolgreich geändert');

                return $this->redirectToRoute('app_dashboard');
            }
        }

        return $this->render('user/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        
        $users = $this->container->get('security.token_storage')->getToken()->getUser();
        if ( $users->getCompany()->getId() == $user->getCompany()->getId() AND in_array("ROLE_HR", $users->getRoles())){
            return $this->render('user/show.html.twig', [
                'user' => $user,
            ]);  
        }
        else{
            return $this->render('user/show.html.twig', [
                'user' => $users,
            ]); 
        }
        
    }
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $users = $this->container->get('security.token_storage')->getToken()->getUser();
        if ( $users->getCompany()->getId() == $user->getCompany()->getId() AND in_array("ROLE_HR", $users->getRoles())){
            //dump($user);
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);
            return $this->render('user/edit.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]);  
        }
        else{ 
            $form = $this->createForm(UserType::class, $users);
            $form->handleRequest($request);
                return $this->render('user/edit.html.twig', [
                    'user' => $users,
                    'form' => $form->createView(),
                ]); 
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
    }
    #[Route('/{id}/document', name: 'app_user_document', methods: ['GET', 'POST'])]
    public function document(Request $request, User $user, ManagerRegistry $doctrine): Response
    {
        $document = new UserDokumente();
        $form = $this->createForm(UserDokumenteType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           $titel =  $form->get('titel')->getData();
           $date = time();

            $document->setUser($user);
            $document->setUplodeTime(new \DateTime());
           
            $file = $form->get('path')->getData();
            $filename = $titel."_".$date;
            $path = 'images/user/dokumente/'.$user;
            $file->move($path, $filename);
            $document->setPath($path . '/' . $filename);
            
            $entityManager = $doctrine->getManager();
            $entityManager->persist($document);
            $entityManager->flush();
           
            return $this->redirectToRoute('app_user_document_list', ['id' => $user->getId()]);
        }

        return $this->render('user/upload_document.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
    #[Route('/{id}/document_show', name: 'app_user_document_list')]
 
    public function dokumenteAnzeigen(User $user,UserDokumenteRepository $userDokumenteRepository )
{
    $dokumente = $userDokumenteRepository->findBy(['user' => $user]);
    return $this->render('user/dokumente.html.twig', [
        'dokumente' => $dokumente,
        'user' => $user,
    ]);
}
    #[Route('/{id}/edit-role', name: 'edit_role', methods: ['GET', 'POST'])]
    public function editRole(User $user, Request $request,ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordEncoder)
    {
        // nur an die ROLE_USER Role Freigegeben
        $this->denyAccessUnlessGranted('ROLE_USER', $user);

        $form = $this->createForm(EditRoleType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em=$doctrine->getManager();
           
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'User role successfully updated');

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/edit_role.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
    #[Route('/{id}/contrect', name: 'app_user_contrect', methods: ['GET', 'POST'])]
    public function contrect( User $user, Request $request, ManagerRegistry $doctrine)
    {
         // nur an die ROLE_USER Role Freigegeben
         $this->denyAccessUnlessGranted('ROLE_USER', $user);
         $form = $this->createFormBuilder()
         ->add('Vertrag', EntityType::class,[
             'class' => Vertrag::class])
         ->add('show', SubmitType::class)
         ->getForm();
         
         $form->handleRequest($request);
        
         if ($form->isSubmitted() ) {
            $contracts = $form->getData();
            $objekt = $user->getObjekt();
            $variableRepos = $doctrine->getRepository(VertragVariable::class);
            $variables = $variableRepos->findAll();
            $replacedContracts = [];
              
            foreach ($contracts as $contract) {
                $text = $contract->getText();
                // Ersetze alle Variablen im Text durch ihre Werte aus $user
                foreach ($variables as $variable) {
                    $value = $this->getPropertyValue($user, $variable->getVar());
                    $text = str_replace('$'.$variable->getVar(), $value, $text);
                }
                $replacedContracts[] = ['text' => $text]; 
            }
            $text = implode("\n\n", array_column($replacedContracts, 'text')); 
            $contract= $contracts['Vertrag'];  
            
            $form = $this->createFormBuilder(null, [
                'action' => $this->generateUrl('app_user_contrect_save', ['id' => $user->getId()])
            ])
            ->add('text', CKEditorType::class, [
                'data' => $text,
                'label' => false,
                'attr' => [
                    'id' => 'form_textarea',
                    'name' => 'editor',
                ]
            ])
            ->add('Save', SubmitType::class)
            ->add('contract', HiddenType::class, [
                'data' => $contract->getId(),
            ])
            ->getForm();
           
            return $this->render('user/contrect.html.twig', [
                'form' => $form->createView(),
                'contracts' => $replacedContracts,
                'data'  => $contract,
                'user' => $user,
                'variablen' => $variables,
                
            ]);
        }
        return $this->render('user/contrect_form.html.twig', [
            'form' => $form->createView(),
            
        ]);
    }
    #[Route('/{id}/contrect_save', name: 'app_user_contrect_save', methods: ['GET', 'POST'])]
    public function contrect_save( User $user, Request $request)
    {
        
        $password = 'texmex';
        $form = $request->get('form');
        $text = $form['text'];
        $contract = $form['contract'];
        // Erstelle TCPDF-Objekt
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // Setze Dokumentinformationen
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Jens Smit');
        $pdf->SetTitle('Document');
        $pdf->SetSubject('Document subject');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // Setze Passwort
        $pdf->SetProtection(['print', 'copy'], $password);

        // Füge Seite hinzu
        $pdf->AddPage();

        // Schreibe Text auf PDF
        // Schreibe HTML-Text auf PDF
        $html = '<html><body>' . $text . '</body></html>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $path = $this->getParameter('kernel.project_dir').'/public/data/'.$user->getId();

        // Überprüfe, ob der Ordner vorhanden ist
        if (!is_dir($path)) {
            // Erstelle den Ordner, falls er nicht vorhanden ist
            mkdir($path, 0755, true);
        }
        // Ausgabe PDF
        $pdf->Output($path.'/'.$contract.'_'.date("Y-m-d",time()).'.pdf', 'F');
        // setze den Status der Vertragsdaten auf 1 -> 1 = Aktiv , 0 = Entwurf
        
      
      
        return $this->render('user/contrect_save.html.twig', [
            'text' => $text
        ]);

    }
    // Hilfsfunktion zum Lesen der Eigenschaft eines Objekts
    private function getPropertyValue($object, $propertyName) {
        $propertyGetter = 'get' . ucfirst($propertyName);
        if (method_exists($object, $propertyGetter)) {
            return $object->$propertyGetter();
        }
        return null;
    }
}
