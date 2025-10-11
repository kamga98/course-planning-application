<?php

namespace App\Controller;

use Calendar\Events;
use App\Calendar\Month;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestController extends AbstractController
{     
  

     /**
     * @Route("/api/test_date", name="api_test_date", methods={"GET"})           
     */
    public function test_date()                                                              
    {              

                  
      try {     
        $events = new Events();     
        $month = new Month($_GET['month'] ?? null, $_GET['year'] ?? null ); 
        $start = $month->getStartingDay();        
        // Si le 1er jour du mois est un lundi on garde ce jour sinon on utilise celui du mois précédent               
        $start = $start->format('N') === '1' ? $start : $month->getStartingDay()->modify('last monday');         

        $weeks = $month->getWeeks();                                                                                           
        $end = (clone $start)->modify("+" . (6 + 7 * ($weeks - 1)) . " days");                                                     
        $events = $events->getEventsBetween($start, $end);                                                       

    } catch (\Exception $e){                                         
        $month = new Month();                                    
    }       
    
      return new Response($weeks);                        
        
    }

    
}
