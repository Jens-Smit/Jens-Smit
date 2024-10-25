<?php

namespace App\Controller;

use App\Repository\UserRepository;
use DateInterval;
use DatePeriod;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/* The `WidgetController` class in PHP calculates and compares a user's total working hours for the
current week with contracted hours, and also displays attendance-related statistics. */
#[Route('/widget')]
class WidgetController extends AbstractController
{

    /**
     * This PHP function calculates the total working hours of a user for the current week and compares
     * it with the contracted hours.
     * 
     * Args:
     *   userRepository (UserRepository): The code snippet you provided is a PHP function that
     * calculates the total working hours of a user for the current week and compares it with the
     * contracted hours. It retrieves the user's data using the UserRepository service and then
     * iterates over the user's Arbeitszeiten (working times) to calculate the total hours
     * 
     * Returns:
     *   The code snippet provided is a PHP Symfony controller method for handling a route related to
     * calculating working hours for a user within a specific week.
     */
    #[Route('/', name: 'app_widget_arbeitszeit')]
    public function arbeitszeit(UserRepository $userRepository): Response
    {
     $id = $_POST['id']; 
      
       $user = $userRepository->find($id);
       $Arbeitszeiten = $user->getArbeitszeits();
       $contH = 0;
       $conti =  0;
       $vertragsStunden = 0;
       foreach ($Arbeitszeiten as $Arbeitszeit){
        $Eintrittszeit = $Arbeitszeit->getEintrittszeit();
        $Austrittszeit = $Arbeitszeit->getAustrittszeit();
        $datum  = $Arbeitszeit->getDatum();
        $heute = new DateTime();
        $tagDerWoche = $heute->format('N'); // ISO-8601 numerische Darstellung des Wochentags (1 für Montag, 7 für Sonntag)
        // Montag dieser Woche
        $montag = clone $heute;
        $montag->modify('-' . ($tagDerWoche - 1) . ' days');
        // Sonntag dieser Woche
        $sonntag = clone $heute;
        $sonntag->modify('+' . (7 - $tagDerWoche) . ' days');
        if ($datum >= $montag && $datum <= $sonntag) {
            $intervall = date_diff($Eintrittszeit, $Austrittszeit);
            $contH += $intervall->h;
            $conti +=  $intervall->i;
        }
       }
       $StdausMinuten = intdiv($conti, 60);
       $restMinuten = $conti % 60;
       $Stunden = $contH+ $StdausMinuten+($restMinuten/60);
       $contractDatas = $user->getContractData();
       foreach ($contractDatas as $contractData){
        if($contractData->getStatus() ==='aktiv'){
            $vertragsStunden = $contractData->getStunden();
        }
       };
        return $this->render('widget/index.html.twig', [
            'user' =>  $user,
            'stunden' => $Stunden,
            'vertragsStunden' => $vertragsStunden
        ]);
    }
    /**
     * The function `anwesenheit` calculates and displays various statistics related to a user's
     * attendance and work hours.
     * 
     * Args:
     *   userRepository (UserRepository): The code you provided is a PHP function that calculates
     * attendance-related statistics for a user based on their work hours, absences, and contract
     * details. Here's a breakdown of what the function does:
     * 
     * Returns:
     *   The `anwesenheit` function is returning a Response object that renders a Twig template named
     * 'widget/anwesenheit.html.twig'. The function passes an array of data to the template, including
     * the user object, calculated percentages for hours worked, vacation time, school time, and sick
     * leave. These percentages are calculated based on the user's work hours, contract data, and
     * absence records.
     */
    #[Route('/anwesenheit', name: 'app_widget_anwesenheit')]
    public function anwesenheit(UserRepository $userRepository): Response
    {
       $id = $_POST['id']; 
       
       $heute = new DateTime();
       $user = $userRepository->find($id);
       $Arbeitszeiten = $user->getArbeitszeits();
       $contH = 0;
       $conti =  0;
       $contKrank = 0;
       $contUrlaub = 0;
       $contSchule  = 0;
       $vertragsStunden = 0;
        function istDatumImAktuellenJahr($datum) {
            // Aktuelles Jahr ermitteln
            $aktuellesJahr = date('Y');
            // Übergebenes Datum in ein DateTime-Objekt umwandeln
            $datumObj = new DateTime($datum);
            // Jahr aus dem DateTime-Objekt extrahieren
            $jahrVomDatum = $datumObj->format('Y');
            // Vergleichen, ob das Jahr des übergebenen Datums mit dem aktuellen Jahr übereinstimmt
            if($jahrVomDatum === $aktuellesJahr){
                return true;
            }else{
                return false;
            }
        }
        function countWorkdays($startDate, $endDate) {
            
           
        
            $interval = new DateInterval('P1D');
            $dateRange = new DatePeriod($startDate, $interval, $endDate);
        
            $workdays = 0;
            foreach ($dateRange as $date) {
                if ($date->format('N') < 6) { // 6 und 7 sind Samstag und Sonntag
                    $workdays++;
                }
            }
        
            return $workdays;
        }
        foreach ($Arbeitszeiten as $Arbeitszeit){
        $Eintrittszeit = $Arbeitszeit->getEintrittszeit();
        $Austrittszeit = $Arbeitszeit->getAustrittszeit();
        $datum  = $Arbeitszeit->getDatum();
        $status = $Arbeitszeit->getFehlzeit();
        if(istDatumImAktuellenJahr($datum->format("Y-m-d")) ===true){
            
            $intervall = date_diff($Eintrittszeit, $Austrittszeit);
            if($status === null){
                $contH += $intervall->h;
                $conti +=  $intervall->i;    
            }else{
                if($status->getId() === 1){
                    $contUrlaub ++;
                }elseif($status->getId() === 3|| $status->getId() === 2){
                    $contKrank  ++;
                }elseif($status->getId() === 4){
                    $contSchule ++;
                }
               
            }
            
       
        }
       }
       $StdausMinuten = intdiv($conti, 60);
       $restMinuten = $conti % 60;
       $Stunden = $contH+ $StdausMinuten+($restMinuten/60);
       $contractDatas = $user->getContractData();
       
       foreach ($contractDatas as $contractData){
        if($contractData->getStatus() ==='aktiv'){
            $vertragsStunden = $contractData->getStunden();
            $startDate = $contractData->getStartDate();
            $weekWhorkdays = $contractData->getArbeitstage();
            $arbeitsstundeProTage= $vertragsStunden/$weekWhorkdays;
            if(istDatumImAktuellenJahr($startDate->format("y-m-d")) ===true){
               $arbeitstagegesamt = countWorkdays($startDate, $heute);
               
            }
            else{
                $date = new DateTime();
                $date->setDate($heute->format('Y'), 1, 1);
                $date->setTime(0, 0);
                $arbeitstagegesamt = countWorkdays($date, $heute);
            }
        }
       };
       $sollArbeiststunden =$arbeitstagegesamt*$arbeitsstundeProTage;

    
        return $this->render('widget/anwesenheit.html.twig', [
            'user' =>  $user,
            'stunden' => $Stunden/$sollArbeiststunden*100,
            
            'urlaub' => ($contUrlaub*$arbeitsstundeProTage/$sollArbeiststunden*100)+($Stunden/$sollArbeiststunden*100),
            'schule' => ($contSchule*$arbeitsstundeProTage/$sollArbeiststunden*100)+($contUrlaub*$arbeitsstundeProTage/$sollArbeiststunden*100)+($Stunden/$sollArbeiststunden*100),
            'krank' =>  ($contKrank*$arbeitsstundeProTage/$sollArbeiststunden*100)+($contSchule*$arbeitsstundeProTage/$sollArbeiststunden*100)+($contUrlaub*$arbeitsstundeProTage/$sollArbeiststunden*100)+($Stunden/$sollArbeiststunden*100),
        ]);
    }
    
}
