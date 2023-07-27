<?php

namespace App\Controller;

use App\Entity\Area;
use App\Entity\Dienstplan;
use App\Entity\Objekt;
use App\Entity\OpeningTime;
use App\Entity\RentItems;
use App\Entity\SpecialOpeningTime;
use App\Form\AreaType;
use App\Form\DienstplanType;
use App\Form\ObjektType;
use App\Form\OpeningTimeType;
use App\Form\RentItemsType;
use App\Form\SpecialOpeningTimeType;
use App\Repository\AreaRepository;
use App\Repository\CompanyRepository;
use App\Repository\DiensteRepository;
use App\Repository\DienstplanRepository;
use App\Repository\ItemCategoriesRepository;
use App\Repository\ObjektRepository;
use App\Repository\OpeningTimeRepository;
use App\Repository\RentItemsRepository;
use App\Repository\SpecialOpeningTimeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

#[Route('/objekt')]
class ObjektController extends AbstractController
{   
   
    #[Route('/', name: 'app_objekt_index', methods: ['GET'])]
    public function index(CompanyRepository $companyRepository, ObjektRepository $objektRepository): Response
    {
        // Aktuellen Benutzer abrufen
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        // Unternehmen für Benutzer abrufen - Admin eines Unternehmens
        $companies = $companyRepository->findBy(['onjekt_admin' => $user]);

        if (empty($companies)) {
            // Wenn der Benutzer kein Admin eines Unternehmens ist, verwende das eigene Unternehmen
            $objekt  = $this->container->get('security.token_storage')->getToken()->getUser()->getObjekt();
            $objekts = [0 => $objekt];
            
            if (empty($objekts[0])){
               
                return $this->redirectToRoute('app_company_new');
            }else{
               // dump($objekts);
                return $this->render('objekt/index.html.twig', [   
                    'objekts' => $objekts
                ]);
            }
        } else {
            // Alle Objekte für die Unternehmen mit einer einzigen Abfrage mittels IN-Operator abrufen
            $companyIds = array_map(function($company) { return $company->getId(); }, $companies);
            $objekts = $objektRepository->findBy((['company' => $companyIds]));
            if (empty($objekts)){
                
                return $this->redirectToRoute('app_objekt_new');
            }else{
                
            return $this->render('objekt/index.html.twig', [   
                'objekts' => $objekts
            ]);
            }
        }

    }
    #[Route('/new', name: 'app_objekt_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ObjektRepository $objektRepository, Environment $twig): Response
    {
        $objekt = new Objekt();
        $form = $this->createForm(ObjektType::class, $objekt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bild = $request->files->get('objekt')['bild'];

           if($bild != null){
            $bildname = md5(uniqid()).'.'.$bild->guessClientExtension();
           
           $bild->move(
                $this->getParameter('bilder_ordner'),
                $bildname
           );
           $objekt->setBild($bildname);
         }
            $objektRepository->save($objekt, true);

            return $this->redirectToRoute('app_objekt_index', [], Response::HTTP_SEE_OTHER);
        }

        return new Response($twig->render('objekt/new.html.twig', [
            'objekt' => $objekt,
            'form' => $form->createView()
        ]));
    }
    #[Route('/rent-items/{id}', name: 'rent_items')]  
    public function rent_items(Request $request ,Objekt $string, AreaRepository $areaRepository)
    {   $item = new RentItems();
        $item_form = $this->createForm(RentItemsType::class, $item);
        $item_form->handleRequest($request);
        $objekt  = $string;
        $areas = $areaRepository->findBy(['objekt'=> $objekt]);
        return $this->render('rent_items/edit_area_layout.html.twig', [
            'Areas' => $areas,
            'form' => $item_form->createView(),
            'objket' => $objekt
        ]);
    }
    #[Route('/rent-items/{item}/update_size_item', name: 'update_size_item')]
    public function UpdateSizeItem(ObjectManager $manager, Request $request)
    {
        $id = $request->request->get('id');
        $width = $request->request->get('width');
        $height = $request->request->get('height');

        $rentItem = $manager->getRepository(RentItems::class)->find($id);
        $rentItem->setSize(['width' => $width, 'height' => $height]);
        $manager->flush();

        return new JsonResponse();
    }
    #[Route('/rent-items/{id}/update_size_page', name: 'update_size_page')]
    public function UpdateSizePage(ObjectManager $manager, Request $request)
    {
        $id = $request->request->get('id');
        $width = $request->request->get('width');
        $height = $request->request->get('height');
        $id =substr($id , 5);
        $area = $manager->getRepository(Area::class)->find($id);
        $area->setSize(['width' => $width, 'height' => $height]);
        $manager->persist($area);
        $manager->flush();

        return new JsonResponse();
    }
    
     #[Route('/rent-items/{id}/update_position', name: 'update_position')]
    public function updatePosition(ObjectManager $manager, Request $request)
    {
        $id = $request->request->get('id');
        $top = $request->request->get('top');
        $left = $request->request->get('left');

        $rentItem = $manager->getRepository(RentItems::class)->find($id);
        $rentItem->setPosition(['top' => $top, 'left' => $left]);
        $manager->flush();

        return new JsonResponse();
    }
    
    #[Route('/ajax_AreaEditSave/{id}', name: 'ajax_AreaEditSave', methods: ['GET', 'POST'])]
    public function ajax_AreaEditSave(Area $area, Request $request, AreaRepository $areaRepository): Response
    {
        
        $form = $this->createForm(AreaType::class, $area);
        $form->handleRequest($request);
        $objekt_id = $area->getObjekt()->getId();
        $bild = $request->files->get('area')['map'];
           if($bild){
                $bildname = md5(uniqid()).'.'.$bild->guessClientExtension();
            }
            $bild->move(
                    $this->getParameter('bilder_ordner'),
                    $bildname
            );
            $area->setMap($bildname);
        $areaRepository->save($area, true);
        return $this->redirectToRoute('rent_items', ['id' => $objekt_id]);

    }
    #[Route('/ajax_area_form_edit', name: 'ajax_area_form_edit', methods: ['GET', 'POST'])]
    public function ajax_area_form_edit(Request $request, AreaRepository $areaRepository): Response
    {
        
        $area = $areaRepository->find(substr($request->request->get('id'),5));
        $form = $this->createForm(AreaType::class, $area);
        $form->handleRequest($request);
        return $this->render('objekt/editFormArea.html.twig', [
            'form' => $form->createView(),
            'area' => $area,
        ]);
    }
    #[Route('/ajax_area_form', name: 'ajax_area_form', methods: ['GET', 'POST'])]
    public function ajax_area_form(Request $request): Response
    {
        $area = new Area();
        $form = $this->createForm(AreaType::class, $area);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
           
           
            //$areaRepository->save($area, true);
 
             return new JsonResponse();
         }
        return $this->renderForm('objekt/newFormArea.html.twig', [
            
            'form' => $form,
        ]);
    }
    #[Route('/ajax_AreaNewSave', name: 'ajax_AreaNewSave', methods: ['GET', 'POST'])]
    public function ajax_AreaNewSave(Request $request, AreaRepository $areaRepository, ObjektRepository $objektRepository , AuthenticationUtils $authenticationUtils): Response
    {  
        $area = new Area(); 
        $form = $this->createForm(AreaType::class, $area);
        $form->handleRequest($request);
        if ($form->isSubmitted() ) {
            $objektId = $_POST['area']['objekt'];
            $objekt = $objektRepository->find($objektId);
          $bild = $request->files->get('area')['map'];
           if($bild){
                $bildname = md5(uniqid()).'.'.$bild->guessClientExtension();
            }
            $bild->move(
                    $this->getParameter('bilder_ordner'),
                    $bildname
            );
            $area->setMap($bildname);
            $areaRepository->save($area, true);
            return $this->redirectToRoute('rent_items', ['id' => $objekt->getId()]);
        }
        return new JsonResponse();
    }
    #[Route('/ajax_item_form', name: 'ajax_item_form', methods: ['GET', 'POST'])]
    public function ajax_item_form(Request $request,TokenStorageInterface $tokenStorage,ManagerRegistry $doctrine, FormFactoryInterface $formFactory): Response
    {   $item = new RentItems();
        $objektId = $request->get("objketId");
     // $objektId = 4;
        $objekt = $doctrine->getRepository(Objekt::class)->find($objektId);
       ;
            
        $choices_areas = [];
            $areas = $doctrine->getRepository(Area::class)->findBy(['objekt' => $objekt]);
            foreach ($areas as $area) {
                $choices_areas[$area->getName()] = $area;
            }
        $form = $formFactory->createBuilder(FormType::class, $item)
            ->add('id', NumberType::class, [
                'required' => false,
            ])
            ->add('name')
            ->add('description')
            ->add('pax')
            ->add('Category')
            ->add('objekt', EntityType::class, [
                'class' => Objekt::class,
                'data' =>  $objekt,
                
                
                'label' => false,
                'attr' => [
                    'style' => 'display:none',
                ],          
            ])
            ->add('area', EntityType::class, [
                'class' => Area::class,
                    'choices' => $choices_areas,
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Area',
                    'attr' => [
                        'class' => 'area-select',
                        
                    ],
                ])
            ->add('status')
                ->add('save', SubmitType::class, [
                    'label' => 'save',
                    'attr' => [
                        'class' => 'btn btn-info w-100',
                        'name' => 'save',
                    ],
            ])
            ->add('save', SubmitType::class);
        $form = $form->getForm();

        return $this->render('objekt/newForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/ajax_item_edit', name: 'ajax_item_edit')]
    public function ajax_item_edit(Request $request,ManagerRegistry $doctrine, RentItemsRepository $rentItemsRepository,FormFactoryInterface $formFactory): Response
    {   $item_id = $request->get("item");
        //$item_id = 5;
      
        
        $item = $rentItemsRepository->find($item_id);
        $objekt = $item->getObjekt();
        $choices_areas = [];
        $areas = $doctrine->getRepository(Area::class)->findBy(['objekt' => $objekt]);
        foreach ($areas as $area) {
            $choices_areas[$area->getName()] = $area;
        }
        $form = $formFactory->createBuilder(FormType::class, $item)
            ->add('id', NumberType::class, [
                'required' => false,
            ])
            ->add('name')
            ->add('description')
            ->add('pax')
            ->add('Category')
            ->add('objekt', EntityType::class, [
                'class' => Objekt::class,
                'data' =>  $objekt,
                
                
                'label' => false,
                'attr' => [
                    'style' => 'display:none',
                ],          
            ])
            ->add('area', EntityType::class, [
                'class' => Area::class,
                    'choices' => $choices_areas,
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'Area',
                    'attr' => [
                        'class' => 'area-select',
                        
                    ],
                ])
            ->add('status')
                ->add('save', SubmitType::class, [
                    'label' => 'save',
                    'attr' => [
                        'class' => 'btn btn-info w-100',
                        'name' => 'save',
                    ],
            ])
            ->add('save', SubmitType::class);
          $form = $form->getForm();





        $form->handleRequest($request);
        
        return $this->renderForm('objekt/editForm.html.twig', [
            
            'form' => $form,
        ]);
    }
    
    #[Route('/ajax_ItemUpdate', name: 'ajax_ItemUpdate')]
    public function ajax_ItemUpdate( Request $request,AreaRepository $areaRepository ,ObjektRepository $objektRepository, ItemCategoriesRepository $itemCategoriesRepository ,RentItemsRepository $rentItemsRepository, AuthenticationUtils $authenticationUtils): Response
    {  
        if ($authenticationUtils !== null && isset($_POST['form'])) {
           
            $data = $_POST['form'];
            
            
            $item = $rentItemsRepository->find($data['id']);
            $item->setName($data['name']);
            $item->setDescription($data['description']);
            $item->setPax($data['pax']);
            $objekt = $objektRepository->find($data['objekt']);
            $item->setObjekt($objekt);
            $Category = $itemCategoriesRepository->find($data['Category']) ;
            $item->setCategory($Category);
            $area = $areaRepository->find($data['area']) ;
            $item->setArea($area);
            $rentItemsRepository->save($item, true);   
          /*  */     // Get the ID of the saved item
             
            return new JsonResponse();
        }
        return new JsonResponse();
      
    }
    #[Route('/ajax_ItemNewSave', name: 'ajax_ItemNewSave', methods: ['GET', 'POST'])]
    public function ajax_ItemNewSave( Request $request,AreaRepository $areaRepository ,ObjektRepository $objektRepository, ItemCategoriesRepository $itemCategoriesRepository ,RentItemsRepository $rentItemsRepository, AuthenticationUtils $authenticationUtils): Response
    {  
        if ($authenticationUtils && isset($_POST['form'])) {
           
            $data = $_POST['form'];
            $objekt = $objektRepository->find($data['objekt']);
            $Category = $itemCategoriesRepository->find($data['Category']) ;
            $area = $areaRepository->find($data['area']) ;
            $item = new RentItems();
            $item->setName($data['name']);
            $item->setDescription($data['description']);
            $item->setPax($data['pax']);
            $item->setObjekt($objekt);
            $item->setCategory($Category);
            $item->setArea($area);
            $bezeichnung = $Category->getName();
            $rentItemsRepository->save($item, true);   
            $this->addFlash(
                'success',
                $bezeichnung.' erfolgreich angelegt'
            );
          
          return new JsonResponse($data);
         }  
              // Get the ID of the saved item
             
            
        
      
    }
    
    #[Route('/{id}', name: 'app_objekt_show', methods: ['GET', 'POST'])]
    public function show(Objekt $string, CompanyRepository $companyRepository, ObjektRepository $objektRepository): Response
    {
        $objekt_id = $string->getId();

        // Aktuellen Benutzer abrufen
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        
        // Unternehmen für Benutzer abrufen - Admin eines Unternehmens
        $companies = $companyRepository->findBy(['onjekt_admin' => $user]);
        
        // Wenn der Benutzer kein Admin eines Unternehmens ist, verwende das eigene Unternehmen
        if (empty($companies)) {
            $companies  = $this->container->get('security.token_storage')->getToken()->getUser()->getCompany();
        }
        
        if (!empty($companies)) {
            // Alle Objekte für die Unternehmen mit einer einzigen Abfrage mittels IN-Operator abrufen
            $companyIds = array_map(function($company) { return $company->getId(); }, $companies);
            $objekts = $objektRepository->findBy(['company' => $companyIds]);
        
            // Prüfen, ob das aktuelle Objekt in der Liste der Objekte enthalten ist
            $control = false;
            foreach($objekts as $objekt){
                if($objekt->getId() == $objekt_id){
                    $control = true;
                    break;
                }
            }
        
            // Wenn das Objekt in der Liste enthalten ist, anzeigen
            // Andernfalls ein leeres Objekt anzeigen
            if ($control) {
                return $this->render('objekt/show.html.twig', [   
                    'objekt' => $string
                ]);
            } else {
                return $this->render('dashboard/noroles.html.twig', [   
                    'objekt' => $objektRepository->find(0)
                ]); 
            }
        } else {
            // Wenn keine Unternehmen vorhanden sind, leeres Objekt anzeigen
            return $this->render('dashboard/noroles.html.twig', [   
                'objekt' => $objektRepository->find(0)
            ]); 
        }
    }
    #[Route('/{id}/dienstplan', name: 'app_objekt_dienstplan', methods: ['GET', 'POST'])]
    public function dienstplan(Objekt $Objekt,DienstplanRepository $dienstplanRepository, Request $request  ): Response
    {
        $dienstplan = new Dienstplan();
        $dienstplan->setObjket($Objekt);
        $form = $this->createForm(DienstplanType::class, $dienstplan);
        $form->handleRequest($request);
        $dienstplans = $Objekt->getDienstplans();
        if ($form->isSubmitted() && $form->isValid()) {
            $dienstplanRepository->save($dienstplan, true);

            return $this->redirectToRoute('app_dienstplan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dienstplan/new.html.twig', [
            'dienstplans' => $dienstplans,
            'dienstplan' => $dienstplan,
            'form' => $form,
        ]);

       
    }
    #[Route('/{id}/edit', name: 'app_objekt_edit', methods: ['GET', 'POST'])]
    public function edit(Objekt $Objekt,CompanyRepository $companyRepository, Request $request  ,ObjektRepository $objektRepository): Response
    {
        $objekt_id = $Objekt ->getId();
       
        //aktuelle benutzer
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        //companys des benutzers ermitteln -admin einer Company
        $companies = $companyRepository->findBy((['onjekt_admin' => $user ]));
        $count = count($companies);
        if ($count < 1) { //wenn kein admin einer Company nutze die Company aus User
            $companies  = $this->container->get('security.token_storage')->getToken()->getUser()->getCompany();
        }
        if ($companies != null) {
            foreach ($companies as $company) {
                $company_id = $company->getId();
                if (isset($objekts)) {
                $temp_arra = $objektRepository->findBy((['company' => $company_id ]));
                $objekts = array_merge($objekts, $temp_arra);
                }else{
                $objekts = $objektRepository->findBy((['company' => $company_id ]));
                }
            }
            $control = false;
            foreach($objekts as $objekt){
                $objekt_id_temp = $objekt->getId();
                if($objekt_id_temp == $objekt_id){
                    $control = true;
                }
            }
            if($control === true){
                $form = $this->createForm(ObjektType::class, $Objekt);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                   $bild = $form->get('bild')->getData();
                   // $bild = $request->files->get['bild']['anhang'];
                   
                 
                   if($bild != null){
                        $bildname = md5(uniqid()).'.'.$bild->guessClientExtension(); 
                        $bild->move(
                                $this->getParameter('bilder_ordner'),
                                $bildname
                        );
                        $objekt->setBild($bildname);
                    }

                    $objektRepository->save($Objekt, true);
                    return $this->redirectToRoute('app_objekt_index', [], Response::HTTP_SEE_OTHER);
                }
                return $this->renderForm('objekt/edit.html.twig', [
                    'objekt' => $Objekt,
                    'form' => $form,  
                ]);
             }else{
                return $this->render('dashboard/noroles.html.twig', [   
                    'objekt' => $objektRepository->find(0)
                ]);  
             }
        }else {
            
            return $this->render('objekt/show.html.twig', [   
                'objekt' => $objektRepository->find(0)
            ]);  
        }

       
    }
    #[Route('/{id}/openning', name: 'app_objekt_opening', methods: ['GET', 'POST'])]
    public function openning(Objekt $string,CompanyRepository $companyRepository ,Request $request, SpecialOpeningTimeRepository $specialOpeningTimeRepository ,OpeningTimeRepository $openingTimeRepository, ObjektRepository $objektRepository, UserRepository $userRepository, AuthenticationUtils $authenticationUtils): Response
    {
        $objekt_id = $string ->getId();
       
        //aktuelle benutzer
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        //companys des benutzers ermitteln -admin einer Company
        $companies = $companyRepository->findBy((['onjekt_admin' => $user ]));
        $count = count($companies);
        if ($count < 1) { //wenn kein admin einer Company nutze die Company aus User
            $companies  = $this->container->get('security.token_storage')->getToken()->getUser()->getCompany();
        }
        if ($companies != null) {
            foreach ($companies as $company) {
                $company_id = $company->getId();
                if (isset($objekts)) {
                $temp_arra = $objektRepository->findBy((['company' => $company_id ]));
                $objekts = array_merge($objekts, $temp_arra);
                }else{
                $objekts = $objektRepository->findBy((['company' => $company_id ]));
                }
            }
            $control = false;
            foreach($objekts as $objekt){
                $objekt_id_temp = $objekt->getId();
                if($objekt_id_temp == $objekt_id){
                    $control = true;
                }
            }
            if($control === true){
                $objekt = $objektRepository->findOneBy((['id' => $objekt_id ]));
        $openingTime = new OpeningTime();
        $form = $this->createForm(OpeningTimeType::class, $openingTime);
        $form->handleRequest($request);
        $SpecialOpeningTime =  new SpecialOpeningTime();
        $Sform = $this->createForm(SpecialOpeningTimeType::class, $SpecialOpeningTime);
        $Sform->handleRequest($request);
        $SpecialOpeningTimes = $specialOpeningTimeRepository->findBy(['objket'=>$objekt]);
          
        if ($form->isSubmitted() && $form->isValid()) {
            $SpecialOpeningTimes = $specialOpeningTimeRepository->findBy(['objket' => $objekt]);
            $openingTime->setObjekt($objekt);
            $day = $openingTime->getDay();
            $del_openingTimes = $openingTimeRepository->findBy(['day' => $day]);
        
            foreach ($del_openingTimes as $del_openingTime) {
                if (
                    ($del_openingTime->getStart() >= $openingTime->getStart() && $del_openingTime->getStart() <= $openingTime->getEnd()) ||
                    ($del_openingTime->getEnd() >= $openingTime->getStart() && $del_openingTime->getEnd() <= $openingTime->getEnd()) ||
                    ($del_openingTime->getStart() <= $openingTime->getStart() && $del_openingTime->getEnd() >= $openingTime->getEnd())||
                    ($del_openingTime->getStart() == $openingTime->getStart() && $del_openingTime->getEnd() == $openingTime->getEnd())
                    ) {
                    $openingTimeRepository->remove($del_openingTime, true);
                }
            }
        
            $openingTimeRepository->save($openingTime, true);
        
            $this->addFlash(
                'success',
                'Erfolgreich gespeichert'
            );
        
            $openingTimes = $openingTimeRepository->findBy(['objekt' => $objekt], ['day' => 'ASC']);
            return $this->renderForm('objekt/openingTime.html.twig', [
                'openingTimes' => $openingTimes,
                'form' => $form,
                'SpecialOpeningTimes' => $SpecialOpeningTimes,
                'Sform' => $Sform,
            ]);
        }
        
        
        elseif($Sform->isSubmitted() ){
            $SpecialOpeningTimes = $specialOpeningTimeRepository->findBy(['objket'=>$objekt]);
           
            $openingTimes = $openingTimeRepository->findBy(['objekt' => $objekt],[ 'day' => 'ASC']);
            $SformData = $Sform->GetViewData();
            $start = $SformData->getStart();
            $end  = $SformData->getEnd();
            $close  = $SformData->isClose();
            

            
            $control = false;
            foreach($SpecialOpeningTimes as $SpecialOpeningTime){
                
                if ($SpecialOpeningTime->getDay()->format('Y-m-d') === $SformData->getDay()->format('Y-m-d'))
                {
                    $control = true;
                }
            }
           // dump($SpecialOpeningTime->getDay()->format('Y-m-d') );
           // dump($SformData->getDay()->format('Y-m-d'));
            if($control === true ){
                $this->addFlash(
                    'danger',
                    'Sonderöffnungszeit bereits für den tag eingetragen'
                    );
            }
            elseif( $start === Null AND $end === Null AND $close === false){
                $this->addFlash(
                    'danger',
                    'bitte Sonderöffnungszeit angeben oder den tag als geschlossen kennzeichnen'
                    );
            
            }elseif($end === Null AND $close === false or $start === Null AND $close === false){
                $this->addFlash(
                    'danger',
                    'bitte Sonderöffnungszeit mit start und ende eingeben'
                    );
            }elseif($end === Null AND $start != Null AND $close === true or $start === Null AND $end != Null  AND $close === true or $start != Null AND $end != Null  AND $close === true){
                $this->addFlash(
                    'danger',
                    'bitte Sonderöffnungszeit mit start und ende koledieren sofern diesaer tag als geschlossen gilt'
                    );
            }

            else{
           $SpecialOpeningTime->setObjket($objekt);
           $specialOpeningTimeRepository->save($SpecialOpeningTime, true);
           
            $this->addFlash(
                'success',
                'erfolgreich Spezialöffnungszeiten gespeichert'
                );
            
            }
            return $this->renderForm('objekt/openingTime.html.twig', [
                'openingTimes' => $openingTimes,
                'form' => $form,  
                'SpecialOpeningTimes' => $SpecialOpeningTimes,
                'Sform' => $Sform,  
            ]);
        }
        else{
            
            $openingTimes = $openingTimeRepository->findBy(['objekt' => $objekt],[ 'day' => 'ASC']);
            return $this->renderForm('objekt/openingTime.html.twig', [
                'openingTimes' => $openingTimes,
                'form' => $form,
                'SpecialOpeningTimes' => $SpecialOpeningTimes,
                'Sform' => $Sform, 
                
                
            ]);
            
        }
            }else{
                return $this->render('dashboard/noroles.html.twig', [   
                    'objekt' => $objektRepository->find(0)
                ]);  
            }

        
  
        }
    }
    #[Route('/delat/{id}', name: 'app_objekt_opening_delat', methods: ['GET', 'POST'])]
    public function delat_opening(SpecialOpeningTime $specialOpeningTime, SpecialOpeningTimeRepository $specialOpeningTimeRepository): Response
    {
        $specialOpeningTimeRepository->remove($specialOpeningTime, true);
        return $this->redirectToRoute('app_objekt_opening', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}', name: 'app_objekt_delete', methods: ['POST'])]
    public function delete(Request $request, Objekt $objekt, ObjektRepository $objektRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$objekt->getId(), $request->request->get('_token'))) {
            $objektRepository->remove($objekt, true);
        }

        return $this->redirectToRoute('app_objekt_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/area/{id}/delete', name: 'area_delete', methods: ['GET', 'POST'])]
    public function deleteArea(int $id, AreaRepository $areaRepository)
    {
            $area = $areaRepository->find($id);
            $objekt_id = $area->getObjekt()->getId();
            $areaRepository->remove($area, true);
            return $this->redirectToRoute('rent_items', ['id' => $objekt_id]);
    }
}
