<?php

namespace App\Calendar;

use Symfony\Component\Validator\Constraints\Length;

class Month {   
     
    private $months = ['Janvier', 'Février', 'Mars','Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    public $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']; 
  
    public $month;    
    public $year;   
    
   

    
    /*
     * Month Constructor 
     * @param int $month Le mois compris entre 1 et 12        
     * @param int $year L'année 
     * @throws Exception    
    */
    public function __construct(?int $month = null ,?int $year = null){

       
      
        if ( $month === null || $month < 1 || $month > 12){
            $month = intval(date('m'));       
   
        }
     
        if ( $year === null ){      
            $year = intval(date('Y'));                           

        }
        /*if ($month < 1 || $month > 12){        

            throw new \Exception("Le mois $month n'est pas valide");
        }*/

       //$month = $month % 12;


        /*if ($year < 1970){   

            throw new \Exception("L'année est inférieure à 1970");   
        }*/           

        $this->month = $month;
        $this->year = $year;    
          

    }

    /*
     * Renvoie le 1er jour du mois 
     * @return \DateTime 
    */
    public function getStartingDay (): \DateTime {

        return  new \DateTime("{$this->year}-{$this->month}-01");        
    }



    /*
     * Retourne le mois en toute lettre, (exemple: Mars 2018)  
     * @return string  
    */       
    public function toString (): string {

           return $this->months[$this->month - 1] . ' ' .$this->year;       

    }

    /*
     * Retourne le nombre de semaine du mois    
     * @return int  
    */    
    public function getWeeks () : int {

        $start = $this->getStartingDay(); 
               
        $end = (clone $start)->modify('+1 month -1 day'); 
         
        

        //Si intval($start->format('W') correspond au numéro de la première semaine du mois en cours  
        //Si intval($end->format('W')) correspond au numéro de la dernière semaine du mois en cours        
        //Si intval($end->format('W')) = 44 par exemple c'est qu'il s'agit de la 44e semaine de l'année    
        // $weeks = nbre de semaines         
        $weeks =intval($end->format('W')) - intval($start->format('W')) + 1; 
                        

        if ($weeks < 0){          
            //var_dump($weeks);       
            $weeks = intval($end->format('W'));                    
        }

        return $weeks;                         
    }  

     /*
     *Est-ce que le jour est dans le mois en cours  
     *@param \DateTime $date                  
     * @return bool   
    */    
    public function withinMonth (\DateTime $date) : bool  {

        return $this->getStartingDay()->format('Y-m') === $date->format('Y-m');       
    }


    /*
     * Renvoit le mois suivant        
     * @return Month    
    */    
    public function nextMonth (): Month 
    {

        $month = $this->month + 1; 
        $year = $this->year;

        if ($month > 12){

            $month = 1;  
            $year += 1;  
        }

        return new Month($month,$year); 

    }

     /*
     * Renvoit le mois précédent           
     * @return Month    
    */    
    public function previousMonth (): Month 
    {

        $month = $this->month - 1; 
        $year = $this->year;

        if ($month < 1){      

            $month = 12;  
            $year -= 1;  
        }    

        return new Month($month,$year); 

    }
   
    /*
     * Renvoit le numéro du mois              
    */    
    public function valeur (String $mois): int
    {


        for($i = 0; $i < 12;$i++){
            if( $mois == $this->months[$i]){
                    $val = $i; 
            }
        }

        return $val + 1;       

    }

}




