<?php

namespace App\Controller;

use App\Entity\Arbeitszeit;
use App\Entity\Company;
use App\Entity\Dienstplan;
use App\Entity\Objekt;
use TCPDF;
use App\Entity\User;

use App\Entity\UserDokumente;
use App\Entity\Vertrag;
use App\Entity\VertragVariable;
use App\Form\ArbeitszeitType;
use App\Form\EditRoleType;
use App\Form\UserType;
use App\Form\UserDokumenteType;
use App\Repository\ArbeitszeitRepository;
use App\Repository\CompanyRepository;

use App\Repository\DienstplanRepository;
use App\Repository\FehlzeitenRepository;
use App\Repository\UserDokumenteRepository;
use App\Repository\UserRepository;
use App\Repository\VertragRepository;
use DateTime;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\Persistence\ManagerRegistry;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


/* The UserController class in PHP Symfony handles various user-related functionalities such as
managing user roles, resetting passwords, editing user details, uploading documents, and generating
contracts. */
#[Route('/user', methods: ['GET', 'POST'])]
class UserController extends AbstractController
{



    /**
     * The function retrieves and displays users based on their roles and company affiliation, with
     * special handling for HR and admin users.
     * 
     * Args:
     *   userRepository (UserRepository): The `userRepository` in the code snippet you provided is an
     * instance of a repository class that is responsible for interacting with the database
     * table/entity that stores user data. In this case, it seems to be used to fetch user entities
     * based on certain criteria, such as filtering users by company.
     *   companyRepository (CompanyRepository): The code snippet you provided is a PHP function that
     * serves as a controller action in a Symfony application. It appears to be handling the logic for
     * displaying a list of users based on the current user's role and company affiliation.
     * 
     * Returns:
     *   If the current user has the role "ROLE_HR" or "ROLE_ADMIN", the function will return a
     * rendered template 'user/index.html.twig' with the variable 'users' containing a list of users
     * associated with the current user's company or as an admin. If the current user does not have one
     * of those roles, the function will return a rendered template 'user/show.html.twig' with the
     */
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, CompanyRepository $companyRepository): Response
    {
        //aktuelle benutzer
        // Aktuellen Benutzer abrufen
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if (in_array("ROLE_HR", $user->getRoles()) || in_array("ROLE_ADMIN", $user->getRoles())) {

            $users = [];
            $userId = $user->getId();
            if ($user->getCompany() !== null) {
                $Company_id = $user->getCompany()->getId();
                $users = $userRepository->findBy(['company' => $Company_id]);
            }




            $admins = $companyRepository->findBy(['onjekt_admin' =>  $userId]);
            foreach ($admins as $admin) {
                $adminUser   = $admin->getOnjektAdmin();
                $users[] = $adminUser;
            }
            $users = array_unique($users);

            return $this->render('user/index.html.twig', [
                'users' => $users,

            ]);
        } else {
            return $this->render('user/show.html.twig', [
                'user' => $user,
            ]);
        }
    }


   /**
    * The function "new" creates a new user, generates a random email address, sets a default password,
    * and saves the user to the database.
    * 
    * Args:
    *   request (Request): The code snippet you provided is a Symfony controller action for creating a
    * new user. Let me explain the parameters used in the `new` method:
    *   passEncoder (UserPasswordHasherInterface): The `` parameter in your Symfony
    * controller method `new()` is of type `UserPasswordHasherInterface`. This interface is typically
    * used for hashing user passwords securely. In your code snippet, you are using the ``
    * to hash the user's password before saving it to the database
    *   userRepository (UserRepository): The `` parameter in your Symfony controller
    * function `new` is an instance of the `UserRepository` class. This repository is typically
    * responsible for interacting with your database to perform operations related to the `User`
    * entity, such as saving, updating, deleting, or querying user data.
    * 
    * Returns:
    *   If the form is submitted and valid, the function will return a redirection response to the
    * route named 'app_user_index' with an empty array of parameters and a status code of 303
    * (HTTP_SEE_OTHER). If the form is not submitted or not valid, the function will return a rendered
    * form template 'user/new.html.twig' with the user and form variables passed to it.
    */
    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $passEncoder, UserRepository $userRepository): Response
    {
        function generateRandomString($length)
        {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
            $randomString = '';

            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }

            return $randomString;
        }
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nutzername = $user->getCompany()->getSign() . '-' . $user->getEmail();
            $user->setEmail($nutzername);
            $user->setPassword(
                $passEncoder->hashPassword($user, $user->getCompany()->getSign() . '1234')
            );

            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
 /**
  * The function `role_save` processes a form submission to update user roles in a Symfony application.
  * 
  * Args:
  *   request (Request): The `request` parameter in your `role_save` method is an instance of Symfony's
  * `Request` class, which represents an HTTP request. It allows you to access and manipulate the
  * request data, such as parameters, headers, and content.
  *   doctrine (ManagerRegistry): In the code snippet you provided, the `doctrine` parameter is of type
  * `ManagerRegistry`. This parameter is used to interact with the Doctrine ORM (Object-Relational
  * Mapping) in Symfony. The `ManagerRegistry` provides access to entity managers and connections in
  * your application.
  *   userRepository (UserRepository): The `UserRepository` in the code snippet is a service that
  * allows you to interact with the database table storing user data. In this context, it is used to
  * retrieve a specific user entity based on the user ID provided in the request data. This user entity
  * is then updated with the new roles before
  * 
  * Returns:
  *   The `role_save` function is returning a JsonResponse with the value 'success' when the condition
  * `if (isset())` is true.
  */
    #[Route('/role_save', name: 'app_role_save', methods: ['GET', 'POST'])]
    public function role_save(Request $request, ManagerRegistry $doctrine, UserRepository $userRepository): Response
    {
        $data = $request->request->all();

        if (isset($data)) {
            $data = $request->request->all();
            $roles = $data['edit_role']['roles'];
            $user = $userRepository->find($data['user']);
            $user->setRoles($roles);
            $em = $doctrine->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'User Rechte erfolgreich geändert');
            return new JsonResponse('success');
        }
    }
 /**
  * This PHP function removes a dienstplan assignment for a user based on the provided data.
  * 
  * Args:
  *   request (Request): The `request` parameter in the `dienstplan_remov` function is an instance of
  * the Symfony\Component\HttpFoundation\Request class. It represents the current HTTP request and
  * contains information such as GET and POST parameters, headers, and more.
  *   dienstplanRepository (DienstplanRepository): The `dienstplanRepository` in the provided code
  * snippet is an instance of the `DienstplanRepository` class. This repository is used to interact
  * with the database table/entity that represents Dienstpläne (shift schedules) in your application.
  *   doctrine (ManagerRegistry): The `` parameter in your Symfony controller method
  * `dienstplan_remov` is of type `ManagerRegistry`. This parameter is used to access the entity
  * manager in order to perform database operations like persisting or removing entities.
  *   userRepository (UserRepository): The `userRepository` in the provided code snippet is an instance
  * of `UserRepository` class. It is used to interact with the database table that stores user data. In
  * this specific function `dienstplan_remov`, the `userRepository` is being used to find a user based
  * on the data
  * 
  * Returns:
  *   The code is returning a JSON response with the data that was received in the request.
  */
    #[Route('/dienstplan_remov', name: 'app_dienstplan_remov', methods: ['GET', 'POST'])]
    public function dienstplan_remov(Request $request, DienstplanRepository $dienstplanRepository, ManagerRegistry $doctrine, UserRepository $userRepository): Response
    {
        $data = $request->request->all();
        $user = $userRepository->find($data['user']);
        $dienstplan = $data['dienstplan'];
        $dienstplan = $dienstplanRepository->find($dienstplan);
        $user->removeDienstplan($dienstplan);
        $entityManager = $doctrine->getManager();
        $entityManager->flush();
        $this->addFlash('success', 'Dienstplan zuordnung entfernt');
        return new JsonResponse($data);
    }
   /**
    * This PHP function handles the process of resetting a user's password by generating a new
    * password, hashing it, updating the user's password in the database, and sending an email with the
    * new password to the user.
    * 
    * Args:
    *   request (Request): The `Request` parameter in the `NewPassword` function represents an HTTP
    * request. It contains information about the request made to the server, such as headers,
    * parameters, and content.
    *   doctrine (ManagerRegistry): The `` parameter in your Symfony controller method
    * `NewPassword` is of type `ManagerRegistry`. This parameter is used to interact with the Doctrine
    * ORM (Object-Relational Mapping) in Symfony. The `ManagerRegistry` provides access to entity
    * managers which are responsible for managing the persistence of your
    *   passEncoder (UserPasswordHasherInterface): The `` parameter in your Symfony
    * controller method `NewPassword` is an instance of `UserPasswordHasherInterface`. This interface
    * is used for hashing user passwords securely. In your code, you are using it to hash the newly
    * generated password before updating the user's password in the database.
    *   mailer (MailerInterface): The `mailer` parameter in the code snippet refers to an instance of
    * the `Symfony\Component\Mailer\Mailer\MailerInterface` class. This interface provides methods for
    * sending emails in Symfony applications. In the code, it is used to send an email to the user
    * after their password has been reset.
    * 
    * Returns:
    *   The `NewPassword` function returns a Response object, which is rendered using the
    * `pw_reset.html.twig` template. The template includes a form for resetting a user's password. If
    * the form is submitted and valid, the function checks if the user exists based on the provided
    * email. If the user is found, a new random password is generated, hashed, and set for the user. An
    * email
    */
    #[Route('/password_reset', name: 'password_reset', methods: ['GET', 'POST'])]
    public function NewPassword(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passEncoder, MailerInterface $mailer): Response
    {

        $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Passwort zurücksetzen',
                'attr'  => ['class' => 'w-100 btn btn-success btn-lg']
            ])
            ->getForm();
        $form->handleRequest($request);


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
            } else {
                $plainPassword = substr(md5(microtime()), 0, 8);
                $encodedPassword = $passEncoder->hashPassword($user, $plainPassword);
                $user->setPassword($encodedPassword);
                $user->setValidPassword(new DateTime());
                $entityManager->persist($user);
                $entityManager->flush();
                $email = (new Email())
                    ->from('info@tex-mex.de')
                    ->to($user_email)
                    ->subject('Ihre Password wurde zurückgesetzt')
                    ->text('Ihre Password wurde zurückgesetzt')
                    ->html('<p>Ihr Passwort ist <b>' . $plainPassword . '</b></p>');

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
   /**
    * The function `changePassword` in PHP Symfony framework allows users to update their password
    * securely by verifying the old password and storing the new password encrypted in the database.
    * 
    * @param ManagerRegistry doctrine In the provided code snippet, the `` parameter is of
    * type `ManagerRegistry`. In Symfony, the `ManagerRegistry` is a service that provides access to
    * entity managers. It allows you to retrieve the entity manager for a specific entity class.
    * @param Request request The `` parameter in the `changePassword` method is an instance of
    * the `Request` class in Symfony. It represents an HTTP request and contains information about the
    * request such as GET and POST parameters, headers, and more. In this context, the ``
    * parameter is used to handle
    * @param UserRepository userRepository The `userRepository` parameter in the `changePassword`
    * method is an instance of the `UserRepository` class. This class is typically used to interact
    * with the database table that stores user information. In this context, it is likely used to
    * retrieve the current user's information from the database in order to
    * @param UserPasswordHasherInterface passwordEncoder The `` parameter in the
    * provided code snippet is an instance of `UserPasswordHasherInterface`. This interface is used for
    * encoding and verifying passwords for user authentication in Symfony applications. It provides
    * methods for hashing passwords and checking if a given password matches the hashed value stored in
    * the database for a
    * 
    * @return If the form is submitted and valid, the function will return a redirect response to the
    * 'app_dashboard' route after successfully changing the user's password and displaying a success
    * flash message. If the form is not submitted or not valid, the function will return a rendered
    * template 'user/change_password.html.twig' with the form to input the new password.
    */
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
            } else {
                $encodedPassword = $passwordEncoder->hashPassword($user, $data['new_password']);
                // Setze das neue, verschlüsselte Passwort für den Benutzer

                // Speichere den Benutzer in der Datenbank
                $user->setPassword($encodedPassword);
                $user->setValidPassword(null);
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
   /**
    * The function `delate_dokument` deletes a document file and its corresponding database entry after
    * checking for its existence.
    * 
    * @param ManagerRegistry doctrine In the provided code snippet, the `` parameter is an
    * instance of `ManagerRegistry` which is used for managing entities and connections in Doctrine
    * ORM. It provides access to entity managers and connections.
    * @param Request request The code you provided is a PHP function that handles the deletion of a
    * document. It takes in three parameters: ``, ``, and ``.
    * @param UserDokumenteRepository userDokumenteRepository The `userDokumenteRepository` in the
    * provided code snippet is an instance of `UserDokumenteRepository`, which is likely a custom
    * repository class responsible for handling database operations related to user documents. In this
    * context, it is used to find the document based on the provided ID and
    * 
    * @return The function `delate_dokument` returns a JSON response with different messages based on
    * the outcome of the deletion process. Here are the possible return messages:
    */
    #[Route('/delate_dokument', name: 'delate_dokument', methods: ['GET', 'POST'])]
    public function delate_dokument(ManagerRegistry $doctrine, Request $request, UserDokumenteRepository $userDokumenteRepository)
    {
        $id = $request->request->get('id');
        $document  = $userDokumenteRepository->find($id);

        // Überprüfen Sie, ob das Dokument gefunden wurde
        if (!$document) {
            return new JsonResponse('Das Dokument wurde nicht gefunden.', 404);
        }
        // Holen Sie den Dateipfad des Dokuments
        $file = $document->getPath();

        if (!file_exists($file)) {
            return new Response('Die Datei existiert / nicht.' . $file);
        }
        // Löschen Sie die Datei
        if (unlink($file)) {
            // Löschen Sie das Dokument aus der Datenbank
            $entityManager = $doctrine->getManager();
            $entityManager->remove($document);
            $entityManager->flush();

            return new JsonResponse('Das Dokument wurde erfolgreich gelöscht.');
        } else {
            return new JsonResponse('Fehler beim Löschen des Dokuments.', 500);
        }
    }
    /**
     * This PHP function shows user details based on certain conditions related to company and user
     * roles.
     * 
     * Args:
     *   user (User): The code snippet you provided is a Symfony controller method for showing user
     * details. Here's a breakdown of the parameters used in the method:
     * 
     * Returns:
     *   The code is checking if the currently logged in user has the "ROLE_HR" role and belongs to the
     * same company as the user being requested. If both conditions are met, it will return the user
     * details specified in the 'user/show.html.twig' template. If the conditions are not met, it will
     * return the details of the currently logged in user instead.
     */
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {

        $users = $this->container->get('security.token_storage')->getToken()->getUser();
        if ($users->getCompany()->getId() == $user->getCompany()->getId() and in_array("ROLE_HR", $users->getRoles())) {
            return $this->render('user/show.html.twig', [
                'user' => $user,
            ]);
        } else {
            return $this->render('user/show.html.twig', [
                'user' => $users,
            ]);
        }
    }
   /**
    * This PHP function deletes a user based on the provided ID after validating the CSRF token.
    * 
    * @param Request request The `` parameter in the `delete` method is an instance of the
    * Symfony\Component\HttpFoundation\Request class. It represents an HTTP request and contains
    * information such as the request method, headers, parameters, and more.
    * @param User user The `user` parameter in the `delete` method represents an instance of the `User`
    * entity class. It is automatically resolved by Symfony's dependency injection container based on
    * the route parameter `{id}` in the route definition. The `User` entity object with the
    * corresponding `id` specified in the
    * @param UserRepository userRepository The `userRepository` parameter in the `delete` method is an
    * instance of the `UserRepository` class. It is used to interact with the database and perform
    * operations related to the `User` entity, such as removing a user from the database.
    * 
    * @return Response The `delete` method is returning a redirection response to the `app_user_index`
    * route with an empty array of parameters and a status code of `303 See Other`.
    */
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

   /**
    * The above function in PHP is responsible for editing user details based on certain conditions
    * related to user roles and company associations.
    * 
    * @param CompanyRepository companyRepository The `companyRepository` is responsible for interacting
    * with the database table that stores company information. In this code snippet, it is used to
    * retrieve company data based on certain conditions, such as finding a company where the
    * 'onjekt_admin' matches the current user.
    * @param UserPasswordHasherInterface passEncoder The `` parameter in the provided code
    * is an instance of `UserPasswordHasherInterface`. This interface is used for hashing user
    * passwords for security purposes. In the `edit` method, it is used to hash the user's password
    * before saving it to the database.
    * @param Request request The code you provided is a Symfony controller action for editing a user.
    * Let me explain the parameters used in this action:
    * @param User user The code you provided is a Symfony controller action for editing a user's
    * information. Let me explain the key points of this code:
    * @param UserRepository userRepository The `userRepository` in the provided code snippet is
    * responsible for interacting with the database to perform operations related to the `User` entity.
    * It seems to handle tasks such as finding a user by email, saving user data, and checking if a
    * username is already taken.
    * @param UserDokumenteRepository userDokumenteRepository The `userDokumenteRepository` in the
    * provided code is used to retrieve documents associated with a user. It is injected into the
    * `edit` method of a controller along with other repositories and services needed for updating user
    * information.
    * 
    * @return Response The `edit` method in the provided code snippet returns a Symfony `Response`
    * object. The response can be one of the following based on the conditions:
    */
    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(CompanyRepository $companyRepository, UserPasswordHasherInterface $passEncoder, Request $request, User $user, UserRepository $userRepository, UserDokumenteRepository $userDokumenteRepository): Response
    {
        $users = $this->container->get('security.token_storage')->getToken()->getUser();
        $company = $companyRepository->findOneBy(['onjekt_admin' => $users]);
        if ($users->getCompany() != null) {
            if (
                $users->getCompany()->getId() == $user->getCompany()->getId() &&
                in_array("ROLE_HR", $users->getRoles()) ||
                $company->getId() == $user->getCompany()->getId() &&
                in_array("ROLE_HR", $users->getRoles())
            ) {
                $form = $this->createForm(UserType::class, $user);
                $form->handleRequest($request);
                $dokumente = $userDokumenteRepository->findBy(['user' => $user]);
                if ($form->isSubmitted() && $form->isValid()) {
                    $data = $form->getData();
                    $currentUser = $userRepository->findOneBy(['email' => $data->getEmail()]);




                    if ($data->getPassword()) {
                        $user->setPassword(
                            $passEncoder->hashPassword($user, $data->getPassword())
                        );
                    }

                    if ($data->getVorname()) {
                        $user->setVorname($data->getVorname());
                    }
                    if ($data->getNachname()) {
                        $user->setNachname($data->getNachname());
                    }
                    if ($data->getAdresse()) {
                        $user->setAdresse($data->getAdresse());
                    }
                    if ($data->getStrasse()) {
                        $user->setStrasse($data->getStrasse());
                    }
                    if ($data->getPlz()) {
                        $user->setPlz($data->getPlz());
                    }
                    if ($data->getOrt()) {
                        $user->setOrt($data->getOrt());
                    }
                    if ($data->getLand()) {
                        $user->setLand($data->getLand());
                    }
                    if ($data->getTelefon()) {
                        $user->setTelefon($data->getTelefon());
                    }
                    if ($data->getSteuernummer()) {
                        $user->setSteuernummer($data->getSteuernummer());
                    }
                    if ($data->getRentenversicherungsnummer()) {
                        $user->setRentenversicherungsnummer($data->getRentenversicherungsnummer());
                    }
                    if ($data->getIBAN()) {
                        $user->setIBAN($data->getIBAN());
                    }
                    if ($data->getObjekt()) {
                        $user->setObjekt($data->getObjekt());
                    }
                    if ($data->getKrankenkasse()) {
                        $user->setKrankenkasse($data->getKrankenkasse());
                    }
                    if ($data->getCompany()) {
                        $user->setCompany($data->getCompany());
                    }
                    if ($currentUser != $user && $currentUser != null) {
                        $this->addFlash(
                            'danger',
                            'Username bereits vergeben'
                        );
                        return $this->render('user/edit.html.twig', [
                            'user' => $user,
                            'form' => $form->createView(),
                            'dokumente' =>  $dokumente,
                        ]);
                    } else {
                        $userRepository->save($user, true);
                        $this->addFlash(
                            'success',
                            'Benutzer erfolgreich gespeichert'
                        );
                        return $this->render('user/edit.html.twig', [
                            'user' => $user,
                            'form' => $form->createView(),
                            'dokumente' =>  $dokumente,
                        ]);
                    }
                }
                return $this->render('user/edit.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                    'dokumente' =>  $dokumente,
                ]);
            } else {
                $form = $this->createForm(UserType::class, $users);
                $form->handleRequest($request);
                $dokumente = $userDokumenteRepository->findBy(['user' => $user]);
                return $this->render('user/edit.html.twig', [
                    'user' => $users,
                    'form' => $form->createView(),
                    'dokumente' =>  $dokumente,
                ]);
            }
        } else {
            $company = $companyRepository->findOneBy(['onjekt_admin' => $users]);
            $user->setCompany($company);
            $userRepository->save($user, true);
            //  dump($user);
            $this->addFlash('success', 'Userdaten erfolgreich gespeichert');

            return $this->redirectToRoute('app_user_index');
        }
    }

   /**
    * The function passwordReset creates a form for users to input a new password and renders a
    * template for changing user passwords.
    * 
    * @param User user The `user` parameter in your `passwordReset` method is an instance of the `User`
    * class. It seems like you are using Symfony and this parameter is being automatically resolved by
    * Symfony's route parameter converter. This means that Symfony will fetch the `User` entity based
    * on the `id`
    * @param Request request The `` parameter in the `passwordReset` method is an instance of
    * the `Symfony\Component\HttpFoundation\Request` class. It represents the current HTTP request that
    * is being handled by the controller. This object contains all the information about the request,
    * such as the request method, headers, query parameters,
    * @param ManagerRegistry doctrine The `ManagerRegistry ` parameter in your `passwordReset`
    * method is used to access the Doctrine EntityManager in Symfony. The `ManagerRegistry` is a
    * service that provides access to named entity managers and connections. It allows you to retrieve
    * the EntityManager for a specific entity manager name.
    * @param UserRepository userRepository The `` parameter in your `passwordReset`
    * method is an instance of the `UserRepository` class. This repository class is typically used to
    * interact with your database and perform operations related to the `User` entity, such as fetching
    * user data, updating user information, and querying the database for
    * @param UserPasswordHasherInterface passwordEncoder The `` parameter in your
    * `passwordReset` method is an instance of `UserPasswordHasherInterface`. This interface is used
    * for encoding and verifying passwords for user authentication. It provides methods for hashing and
    * checking passwords securely.
    * 
    * @return Response A response containing the rendered template
    * 'user/change_user_password.html.twig' with the form and user variables passed to it.
    */
    #[Route('/{id}/passwordReset', name: 'app_user_passwordReset', methods: ['GET', 'POST'])]
    public function passwordReset(User $user, Request $request, ManagerRegistry $doctrine, UserRepository $userRepository, UserPasswordHasherInterface $passwordEncoder): Response
    {

        // Erstelle ein Formular, um das neue Passwort einzugeben
        $form = $this->createFormBuilder()
            ->add('new_password', PasswordType::class, ['label' => 'Neues Passwort'])
            ->add('submit', SubmitType::class, ['label' => 'Passwort ändern'])
            ->getForm();
        return $this->render('user/change_user_password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

   /**
    * The function `dienstplan` retrieves and displays a list of available dienstplans for a user to
    * select and assign to a user.
    * 
    * @param Request request The `Request ` parameter in the `dienstplan` method represents the
    * HTTP request that is being made to the server. It contains information about the request such as
    * headers, parameters, and content.
    * @param User user The code you provided is a Symfony controller action that handles the display
    * and submission of a form related to a user's "Dienstplan" (schedule). Let me explain the key
    * points of the code:
    * @param ManagerRegistry doctrine The `doctrine` parameter in your Symfony controller method
    * `dienstplan` is of type `ManagerRegistry`. This parameter is used to access the entity manager in
    * order to interact with your database. In your code, you are using it to retrieve and persist
    * entities.
    * 
    * @return Response The `dienstplan` method returns a Response object which renders the
    * `user/dienstplan.html.twig` template. The template is rendered with the form created using
    * Symfony's Form component, and it includes the form and the user object to be displayed in the
    * template.
    */
    #[Route('/{id}/dienstplan', name: 'app_user_dienstplan', methods: ['GET', 'POST'])]
    public function dienstplan(Request $request, User $user, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $objekts = $entityManager->getRepository(Objekt::class)->findBy(['company' => $user->getCompany()->getId()]);
        $choices_dienstplan = [];
        foreach ($objekts as $objekt) {
            $objektId = $objekt->getId();
            $dienstplans = $doctrine->getRepository(Dienstplan::class)->findBy(["Objket" => $objektId]);
            foreach ($dienstplans as $dienstplan) {
                $choices_dienstplan[$dienstplan->getBezeichnung()] = $dienstplan->getId();
            }
        }
        // dump($choices_dienstplan);


        $form = $this->createFormBuilder()

            ->add('dienstplan', ChoiceType::class, [
                'choices' => $choices_dienstplan,
                'expanded' => true,
                'multiple' => false,
                'label' => 'Dienstplan',
            ])
            ->add('submit', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $dienstplan = $data['dienstplan'];
            $user->addDienstplan($dienstplan);
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
        }
        return $this->render('user/dienstplan.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * The function `arbeitszeiten` handles GET and POST requests for managing user work hours,
     * including saving, deleting, and creating new work hours entries.
     * 
     * Args:
     *   user (User): The code snippet you provided is a Symfony controller action for managing work
     * hours for a user. It handles GET and POST requests for displaying, editing, and creating work
     * hours entries.
     *   userRepository (UserRepository): The UserRepository is responsible for handling database
     * operations related to the User entity. It typically includes methods for querying, creating,
     * updating, and deleting user records in the database. In the provided code snippet, the
     * UserRepository is used to fetch user-related data when working with the arbeitszeiten (working
     * hours)
     *   doctrine (ManagerRegistry): Doctrine is an object-relational mapping (ORM) tool for PHP that
     * provides transparent persistence for PHP objects. It allows developers to work with databases
     * using PHP objects and provides a way to query and manipulate data without writing SQL queries
     * directly. In the code snippet you provided, the `ManagerRegistry `
     *   arbeitszeitRepository (ArbeitszeitRepository): The `arbeitszeitRepository` in the provided
     * code is an instance of `ArbeitszeitRepository` class. It is responsible for handling database
     * operations related to the `Arbeitszeit` entity, such as fetching, saving, and deleting
     * `Arbeitszeit` records.
     *   request (Request): The code you provided is a Symfony controller action for handling the
     * display and manipulation of work hours for a user. It retrieves the work hours for a specific
     * user, creates forms for each work hour entry, processes form submissions to update or delete
     * work hours, and then renders the view with the updated data.
     * 
     * Returns:
     *   The code snippet provided is a PHP Symfony controller method for handling GET and POST
     * requests related to a user's work hours.
     */
    #[Route('/{id}/arbeitszeiten', name: 'app_user_arbeitszeiten', methods: ['GET', 'POST'])]
    public function arbeitszeiten(User $user,  ManagerRegistry $doctrine, ArbeitszeitRepository $arbeitszeitRepository, Request $request): Response
    {

        $arbeitszeiten = $arbeitszeitRepository->findBy(['user' => $user]);
        $forms = [];
        foreach ($arbeitszeiten as $arbeitszeit) {
            $form = $this->createForm(ArbeitszeitType::class, $arbeitszeit, [
                'form_id' => $arbeitszeit->getId(),
            ]);
            $forms[$arbeitszeit->getId()] = $form->createView();
        }

        foreach ($forms as $form) {
            if ($request->getMethod() == 'POST') {
                $data = $request->request->all()['arbeitszeit'];

                $arbeitszeit = $form->vars['value'];
                $submittedForm = $request->request;
                $formId = intval($submittedForm->all()['arbeitszeit']['form_id']);
                if ($formId == $arbeitszeit->getId()) {

                    if (isset($_POST['arbeitszeit']['save'])) {
                        $dateTime = new DateTime();
                        $array = $data['Eintrittszeit'];
                        $dateTime->setTime($array['hour'], $array['minute']);
                        $arbeitszeit->setEintrittszeit($dateTime);
                        $dateTime = new DateTime();
                        $array = $data['Austrittszeit'];
                        $dateTime->setTime($array['hour'], $array['minute']);
                        $arbeitszeit->setAustrittszeit($dateTime);
                        $entityManager = $doctrine->getManager();
                        $entityManager->persist($arbeitszeit);
                        $entityManager->flush();

                        // Query the database for the saved data
                        $savedData = $arbeitszeitRepository->find($formId);

                        // Verify that the data matches the expected values
                        if ($savedData && $savedData->getId() != null) {
                            $this->addFlash('success', 'Arbeitszeit gespeichert');
                            $arbeitszeiten = $arbeitszeitRepository->findBy(['user' => $user]);
                            $forms = [];
                            foreach ($arbeitszeiten as $arbeitszeit) {
                                $form = $this->createForm(ArbeitszeitType::class, $arbeitszeit, [
                                    'form_id' => $arbeitszeit->getId(),
                                ]);
                                $forms[$arbeitszeit->getId()] = $form->createView();
                            }
                        } else {
                            $this->addFlash('danger', 'Fehler beim Speichern der Arbeitszeit');
                        }
                    } elseif (isset($_POST['arbeitszeit']['delate'])) {
                        $entityManager = $doctrine->getManager();
                        $entityManager->remove($arbeitszeit);
                        $entityManager->flush();
                        $arbeitszeiten = $arbeitszeitRepository->findAll();

                        $this->addFlash('success', 'Arbeitszeit erfolgreich entfernt');
                    } elseif (isset($_POST['arbeitszeit']['newsave'])) {


                        $this->addFlash('success', 'Arbeitszeit erfolgreich angelegt');
                    }
                }
            }
        }

        return $this->render('arbeitszeit/edit.html.twig', [
            'arbeitszeiten' => $arbeitszeiten,
            'form' => $forms,


        ]);
    }
   /**
    * The function `arbeitszeiten_edit` creates a form to input and save work hours and related
    * information for a user, handling form submission and displaying appropriate messages.
    * 
    * @param User user The code you provided is a Symfony controller action for editing work hours for
    * a specific user. The action takes several parameters including a User entity object, repositories
    * for Fehlzeiten, User, and Arbeitszeit entities, ManagerRegistry for managing entities,
    * ArbeitszeitRepository for accessing Arbeitszeit entities, and
    * @param FehlzeitenRepository fehlzeitenRepository The `fehlzeitenRepository` is an instance of
    * `FehlzeitenRepository` class, which is used to interact with the database table storing
    * information about different types of absences or reasons for absence (fehlzeiten). In this
    * context, it is being used to retrieve a
    * @param UserRepository userRepository The `` parameter in the `arbeitszeiten_edit`
    * method is an instance of `UserRepository`. This repository class is typically used to interact
    * with the database table/entity that stores user information. It provides methods for querying,
    * updating, and deleting user records in the database.
    * @param ManagerRegistry doctrine Doctrine is an object-relational mapping (ORM) tool for PHP that
    * provides transparent persistence for PHP objects. In the given code snippet, the `ManagerRegistry
    * ` parameter is used to access the entity manager, which is responsible for managing the
    * lifecycle of entities in the application.
    * @param ArbeitszeitRepository arbeitszeitRepository The `arbeitszeitRepository` in the provided
    * code snippet is an instance of `ArbeitszeitRepository`. It is used to interact with the database
    * table/entity that stores information about work hours (Arbeitszeit) for users.
    * @param Request request The `Request ` parameter in the `arbeitszeiten_edit` method is an
    * instance of Symfony's Request class. It represents an HTTP request and contains information such
    * as the request method, headers, parameters, and more.
    * 
    * @return Response The function `arbeitszeiten_edit` returns a Response object which renders a Twig
    * template named 'arbeitszeit/arbeitszeitNew.html.twig'. The template is rendered with the form
    * created in the function and the user object passed to the function. If the form is submitted and
    * valid, a new Arbeitszeit entity is created and saved to the database. A success flash message is
    * added, and the
    */
    #[Route('/{id}/arbeitszeiten_edit', name: 'app_user_arbeitszeiten_edit', methods: ['GET', 'POST'])]
    public function arbeitszeiten_edit(User $user, FehlzeitenRepository $fehlzeitenRepository,  ManagerRegistry $doctrine,  Request $request): Response
    {
        $arbeitszeit = new Arbeitszeit();
        $arbeitszeit->setUser($user);
        $form = $this->createForm(ArbeitszeitType::class);
        $fehlzeiten = $fehlzeitenRepository->findAll();
        $choices = [];
        $choices['-'] = null;
        foreach ($fehlzeiten as $fehlzeit) {
            $choices[$fehlzeit->getBezeichnung()] = $fehlzeit->getId();
        }
        $form = $this->createFormBuilder(null, [
            'attr' => ['id' => 'fehlzeiten_form']
        ])
            ->add('date', DateType::class, [
                'label' => 'date',
                'data' => (new \DateTime())
            ])
            ->add('kommen', TimeType::class, [
                'label' => 'kommen'
            ])
            ->add('gehen', TimeType::class, [
                'label' => 'gehen'
            ])
            ->add('fehlzeit', ChoiceType::class, [
                'choices' => $choices,

            ])
            ->add('save', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $arbeitszeit->setDatum($form->get('date')->getData());
            $arbeitszeit->setEintrittszeit($form->get('kommen')->getData());
            $arbeitszeit->setAustrittszeit($form->get('gehen')->getData());
            $fehlzeitId = $form->get('fehlzeit')->getData();
            if ($fehlzeitId) {
                $fehlzeit = $fehlzeitenRepository->find($fehlzeitId);
                $arbeitszeit->setFehlzeit($fehlzeit);
            }
            $entityManager->persist($arbeitszeit);
            $entityManager->flush();
            $this->addFlash('success', 'Arbeitszeit erfolgreich angelegt');
            return $this->redirectToRoute('app_user_arbeitszeiten_edit', ['id' => $user->getId()]);
        }
        return $this->render('arbeitszeit/arbeitszeitNew.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
    /**
     * This PHP function retrieves and displays the duty plans associated with a specific user.
     * 
     * @param User user The `user` parameter in the `dienstplan_show` method is of type `User`. It
     * represents the user for whom the dienstpläne (work schedules) are being retrieved. The method
     * uses this parameter to fetch the dienstpläne associated with the user
     * @param ManagerRegistry doctrine In the code snippet you provided, the `ManagerRegistry
     * ` parameter is being injected into the `dienstplan_show` method. In Symfony, the
     * `ManagerRegistry` service is used to manage and retrieve entity managers. It provides access to
     * all entity managers defined in your application.
     * 
     * @return Response The `dienstplan_show` method is returning a Response object that renders the
     * `user/dienstplan_show.html.twig` template. The template is being passed an array with the key
     * `dienstplans` containing the dienstplans associated with the user.
     */
    #[Route('/{id}/dienstplan_show', name: 'app_user_dienstplan_show', methods: ['GET', 'POST'])]
    public function dienstplan_show(User $user): Response
    {
        // ermitteln in welchen dienstplänen der user Steht
        $dienstplans = $user->getDienstplans();
        return $this->render('user/dienstplan_show.html.twig', [

            'dienstplans' => $dienstplans,
        ]);
    }
    /**
     * The function handles the uploading of a document for a specific user in a PHP Symfony
     * application.
     * 
     * @param Request request The `` parameter in the `document` method is an instance of the
     * `Request` class, which represents an HTTP request. It contains information about the request
     * such as headers, parameters, and content.
     * @param User user The code snippet you provided is a Symfony controller action for handling
     * document uploads for a specific user. Let me explain the key parts of the code:
     * @param ManagerRegistry doctrine In the code snippet you provided, the `doctrine` parameter is an
     * instance of `ManagerRegistry`. This parameter is used to interact with the Doctrine ORM
     * (Object-Relational Mapping) in Symfony. The `ManagerRegistry` provides access to entity managers
     * and connections in your Symfony application.
     * @param UserDokumenteRepository userDokumenteRepository The `` parameter
     * in the `document` method is an instance of `UserDokumenteRepository` class. This repository
     * class is typically used to interact with the database table/entity that stores user documents.
     * It provides methods for querying, persisting, and managing user
     * 
     * @return Response If the form is submitted and valid, the function will return a redirect
     * response to the route named 'app_user_document_list' with the user's ID as a parameter. If the
     * form is not submitted or not valid, the function will return a rendered template
     * 'user/upload_document.html.twig' with the form and user data.
     */
    #[Route('/{id}/document', name: 'app_user_document', methods: ['GET', 'POST'])]
    public function document(Request $request, User $user, ManagerRegistry $doctrine): Response
    {
        $document = new UserDokumente();
        $form = $this->createForm(UserDokumenteType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $titel =  $form->get('titel')->getData();
            $date = date("Y-m-d", time());

            $document->setUser($user);
            $document->setUplodeTime(new \DateTime());

            $file = $form->get('path')->getData();
            $filename = $titel . "_" . $date . ".pdf";
            $path = 'data/' . $user->getId();
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            $move_path = 'data/' . $user->getId();
            $file->move($move_path, $filename);
            $document->setPath($path . '/' . $filename);


            $entityManager = $doctrine->getManager();
            $entityManager->persist($document);
            $entityManager->flush();

            //successs medung
            return $this->redirectToRoute('app_user_document_list', ['id' => $user->getId()]);
        }

        return $this->render('user/upload_document.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
   /**
    * This PHP function retrieves documents associated with a user and then redirects to the user edit
    * page.
    * 
    * @param User user The `user` parameter in the `dokumenteAnzeigen` method is an instance of the
    * `User` class. It is being used to fetch documents associated with a specific user from the
    * `UserDokumenteRepository`.
    * @param UserDokumenteRepository userDokumenteRepository The `` parameter
    * in the `dokumenteAnzeigen` method is an instance of the `UserDokumenteRepository` class. This
    * parameter is used to interact with the database and retrieve documents associated with a specific
    * user. In this case, it is
    * 
    * @return In the provided code snippet, the `dokumenteAnzeigen` method is returning a redirection
    * response using the `redirectToRoute` method. It is redirecting to the route named `app_user_edit`
    * with the user's ID as a parameter.
    */
    #[Route('/{id}/document_show', name: 'app_user_document_list', methods: ['GET', 'POST'])]
    public function dokumenteAnzeigen(User $user, UserDokumenteRepository $userDokumenteRepository)
    {
        $dokumente = $userDokumenteRepository->findBy(['user' => $user]);
        return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
    }
    /**
     * The function `editRole` in PHP Symfony framework is used to edit the role of a user,
     * specifically granting access only to users with the 'ROLE_USER' role, updating the user's role
     * in the database, and redirecting to the user index page upon successful role update.
     * 
     * @param User user The `editRole` method in the code snippet is a controller action that handles
     * editing the role of a user. It takes three parameters:
     * @param Request request The `` parameter in the `editRole` method is an instance of the
     * Symfony\Component\HttpFoundation\Request class. It represents the current HTTP request and
     * contains information such as the request method, headers, and request parameters.
     * @param ManagerRegistry doctrine The `` parameter in the `editRole` method is of type
     * `ManagerRegistry`. This parameter is used to access the entity manager in Symfony. The
     * `ManagerRegistry` is a service that provides access to multiple entity managers. In this case,
     * it is used to get the entity manager (`
     * 
     * @return The `editRole` method is returning a rendered template named 'user/edit_role.html.twig'
     * with the form and user variables passed to it. If the form is submitted and valid, it will
     * update the user role in the database, add a success flash message, and redirect to the
     * 'app_user_index' route.
     */
    #[Route('/{id}/edit-role', name: 'edit_role', methods: ['GET', 'POST'])]
    public function editRole(User $user, Request $request, ManagerRegistry $doctrine)
    {
        // nur an die ROLE_USER Role Freigegeben
        $this->denyAccessUnlessGranted('ROLE_USER', $user);

        $form = $this->createForm(EditRoleType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();

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
   /**
    * This PHP function generates a form for a user to view and edit contracts, replacing variables
    * with user-specific values and allowing the user to save the edited contract text.
    * 
    * @param User user The code you provided is a Symfony controller action that handles a form
    * submission for a user's contract. It seems to be generating a form for selecting a contract,
    * replacing variables in the contract text with values from the user object, and then displaying
    * the updated contract text in a CKEditor form field for editing
    * @param Request request The code you provided is a Symfony controller action that handles a form
    * submission for a user's contract. It seems to be rendering a form for the user to input some
    * contract details and then processing the form submission.
    * @param ManagerRegistry doctrine The `doctrine` parameter in your Symfony controller method
    * `contrect` is of type `ManagerRegistry`. This parameter allows you to interact with your database
    * using Doctrine ORM in Symfony. It provides access to entity managers and repositories, allowing
    * you to perform database operations such as fetching entities, persisting data,
    * 
    * @return The `contrect` method returns a rendered template based on certain conditions. If the
    * form is submitted, it processes the form data, replaces variables in the text of contracts, and
    * then renders a template with a new form for saving the modified contract text. If the form is not
    * submitted, it simply renders a template with the initial form for selecting a contract.
    */
    #[Route('/{id}/contrect', name: 'app_user_contrect', methods: ['GET', 'POST'])]
    public function contrect(User $user, Request $request, ManagerRegistry $doctrine)
    {
        // nur an die ROLE_USER Role Freigegeben
        $this->denyAccessUnlessGranted('ROLE_USER', $user);
        $form = $this->createFormBuilder(null, [
            'action' => $this->generateUrl('app_user_contrect', ['id' => $user->getId()])
        ])
            ->add('Vertrag', EntityType::class, [
                'class' => Vertrag::class
            ])
            ->add('show', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
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
                    $text = str_replace('$' . $variable->getVar(), $value, $text);
                }
                $replacedContracts[] = ['text' => $text];
            }
            $text = implode("\n\n", array_column($replacedContracts, 'text'));
            $contract = $contracts['Vertrag'];

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
                ->add(
                    'Password',
                    PasswordType::class,
                    [
                        'label' => 'Passwort',

                    ]
                )
                // TODO: set defult password 
                ->add('Save', SubmitType::class)
                ->add('contract', HiddenType::class, [
                    'data' => $contract->getId(),
                ])
                ->getForm();
            // dump($variables);
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
    /**
     * The function `contrect_save` in a Symfony controller saves a PDF document with password
     * protection and updates contract status for a user.
     * 
     * @param User user The `user` parameter in your Symfony controller action `contrect_save`
     * represents an instance of the `User` entity class. This parameter is used to fetch the user
     * object associated with the current operation. In your code snippet, it is being used to retrieve
     * user-specific information such as the user's
     * @param Request request The `request` parameter in the `contrect_save` function is an instance of
     * the `Request` class in Symfony. It contains information about the current request, such as
     * request parameters, headers, and other request-related data. In this context, it is used to
     * retrieve form data and interact with
     * @param ManagerRegistry doctrine The `doctrine` parameter in your Symfony controller action
     * refers to an instance of `ManagerRegistry`. `ManagerRegistry` is an object that manages the
     * various entity managers in a Symfony application. It provides access to the entity managers and
     * allows you to interact with the database using Doctrine ORM.
     * @param VertragRepository vertragRepository The `vertragRepository` in the provided code snippet
     * is an instance of the `VertragRepository` class. In Symfony applications, repositories are used
     * to interact with the database and perform queries related to specific entities. In this case,
     * the `vertragRepository` is used to find a specific contract
     * 
     * @return The function `contrect_save` in the Symfony controller is returning a redirection
     * response to the route named `app_user_edit` with the user's ID as a parameter. This redirection
     * is performed using the `redirectToRoute` method in Symfony, which redirects the user to another
     * route after the PDF document generation and user document creation operations are completed.
     */
    #[Route('/{id}/contrect_save', name: 'app_user_contrect_save', methods: ['GET', 'POST'])]
    public function contrect_save(User $user, Request $request, ManagerRegistry $doctrine, VertragRepository $vertragRepository)
    {
        //TODO: set Document password defult 1234

        $form = $request->get('form');
        $password = $form['Password'];

        $text = $form['text'];
        $contract = $form['contract'];
        $vertrag = $vertragRepository->find($contract);


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
        $path = $this->getParameter('kernel.project_dir') . '/public/data/' . $user->getId();

        // Überprüfe, ob der Ordner vorhanden ist
        if (!is_dir($path)) {
            // Erstelle den Ordner, falls er nicht vorhanden ist
            mkdir($path, 0755, true);
        }
        // Ausgabe PDF
        $pdf->Output($path . '/' . $vertrag->getTitel() . '_' . date("Y-m-d", time()) . '.pdf', 'F');
        // setze den Status der Vertragsdaten auf 1 -> 1 = Aktiv , 0 = Entwurf
        $document = new UserDokumente();

        $document->setDiscription($vertrag->getDiscription());
        $document->setTitel($vertrag->getTitel());
        $document->setUser($user);
        $document->setUplodeTime(new \DateTime());
        $path = 'data/' . $user->getId();
        $document->setPath($path . '/' . $vertrag->getTitel() . '_' . date("Y-m-d", time()) . '.pdf');
        $entityManager = $doctrine->getManager();
        $entityManager->persist($document);
        $entityManager->flush();



        // Leite auf andere Route weiter
        return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
    }
   /**
    * The function `passwordResetSave` resets and saves a user's password in a Symfony application.
    * 
    * @param Request request The `Request` object contains information about the current request, such
    * as parameters, headers, and other data.
    * @param ManagerRegistry doctrine Doctrine is an object-relational mapping (ORM) tool for PHP that
    * provides transparent persistence for PHP objects. It allows developers to work with databases
    * using PHP objects and provides a powerful query builder for database interactions. In the given
    * code snippet, the `` parameter is an instance of `ManagerRegistry`,
    * @param UserPasswordHasherInterface passEncoder The `` parameter in your code refers
    * to an instance of the `UserPasswordHasherInterface` interface. This interface is typically used
    * for hashing and verifying user passwords securely in Symfony applications.
    * @param User user The code you provided is a Symfony controller action for handling a password
    * reset save operation. Let me explain the parameters used in the method signature:
    * 
    * @return Response a JsonResponse containing the concatenated string of the password data and the
    * user ID.
    */
    #[Route('/{id}/passwordResetSave', name: 'app_user_passwordResetSave', methods: ['GET', 'POST'])]
    public function passwordResetSave(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passEncoder, User $user): Response
    {
        $entityManager = $doctrine->getManager();
        $data = $request->request->get('spw');
        $userId = $user->getId();
        $encodedPassword = $passEncoder->hashPassword($user, $data);
        $user->setPassword($encodedPassword);
        $now = new DateTime();
        $user->setValidPassword($now);
        $entityManager->persist($user);
        $entityManager->flush();
        return new JsonResponse($data . " - " . $userId);
    }
    /**
     * The function `getPropertyValue` is a helper function in PHP used to read the value of a property
     * from an object using its getter method.
     * 
     * @param object The `object` parameter in the `getPropertyValue` function refers to the object
     * from which you want to retrieve the value of a specific property. This function dynamically
     * constructs the getter method name based on the property name provided and then checks if the
     * object has that getter method. If the getter method exists,
     * @param propertyName The `propertyName` parameter in the `getPropertyValue` function is the name
     * of the property whose value you want to retrieve from the object. It is used to dynamically
     * construct the method name to call in order to get the value of that property from the object.
     * 
     * @return If the method `getPropertyValue` is called with an object and a property name, it will
     * return the value of the property from the object if a getter method exists for that property. If
     * a getter method does not exist, it will return `null`.
     */
    // Hilfsfunktion zum Lesen der Eigenschaft eines Objekts
    private function getPropertyValue($object, $propertyName)
    {
        $propertyGetter = 'get' . ucfirst($propertyName);
        if (method_exists($object, $propertyGetter)) {
            return $object->$propertyGetter();
        }
        return null;
    }
}
