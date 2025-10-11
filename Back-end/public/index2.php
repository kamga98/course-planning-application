<!DOCTYPE html>
<html lang="en">   
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/calendar.css">         
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <title>Document</title>
    
</head>
<body>
      <nav class="navbar navbar-dark bg-primary mb-3">
        <a href="/index2.php" class="navbar-brand"> Mon Planning </a>  

    </nav>  
    
          
        <?php


        require '../src/Calendar/Month.php';   
        require '../src/Calendar/Events.php';     


        
        try {
            $events = new Calendar\Events();     
            $month = new App\Calendar\Month($_GET['month'] ?? null, $_GET['year'] ?? null ); 
            $start = $month->getStartingDay();        
            // Si le 1er jour du mois est un lundi on garde ce jour sinon on utilisera celui du mois précédent               
            $start = $start->format('N') === '1' ? $start : $month->getStartingDay()->modify('last monday');         

            $weeks = $month->getWeeks();                                                                                 
        
                
            $end = (clone $start)->modify("+" . (6 + 7 * ($weeks - 1)) . " days");                                                     

            $events = $events->getEventsBetween($start, $end);                                                       

        } catch (\Exception $e){                                         
            $month = new App\Calendar\Month();                          
        }
        ?>                     
           
         <div class="d-flex flex-row align-items-center justify-content-between mx-sm-3">                  
         <h1> <?= $month->toString(); ?> </h1>                              
         <div>
                <a href="/index2.php?month=<?= $month->previousMonth()->month; ?>&year=<?= $month->previousMonth()->year; ?>" class="btn btn-primary"><</a>
                <a href="/index2.php?month=<?= $month->nextMonth()->month; ?>&year=<?= $month->nextMonth()->year; ?>" class="btn btn-primary">></a>                      
         </div>


         </div>

       
                   
   
         <table class="calendar__table calendar__table--<?= $weeks  ; ?>weeks">  

                <?php     
                
                $semaines = json_decode(file_get_contents('semaines.json'), true);  

                //var_dump(intval(substr($semaines[0]['date_debut'],0,-6)));     
                //var_dump(intval(substr($semaines[0]['date_fin'],0,-6)));   
               // var_dump(intval(substr($semaines[0]['date_fin'],3,-3)));
                      
               // var_dump($month->valeur(substr($month->toString(),0,-5))); 
      
                          

                
                for($i = 0; $i < $weeks ; $i++):   ?>
         
       
                <tr>             
       
                <?php  foreach($month->days as $k => $day):    

                                   

                        $date = (clone $start)->modify("+" . ($k + $i * 7) ."days");  
                     
                                                                      

                    ?>     

                    <td class="<?= $month->withinMonth($date) ? '' : 'calendar__othermonth' ; ?>">        
                        <?php if ($i === 0):  ?>                                  
                        <div class="calendar__weekday">  <?= $day ?>    </div>   
 
                        <?php endif;  ?>                      
                        <div class="calendar__day">  <?= $date->format('d');  ?>    
                            
                         
                        <?php  if($day == "Vendredi"): ?>                                                         
                        <form method='POST' action='index2.php'>                       
                        <input type='checkbox' name='case' value='on'> Full                           
                        <a href="https://127.0.0.1:8000/test.js" class="btn btn-primary"><input type='submit' name='semaine' value='View'> </a>
                        
                        </form>  
                        <?php endif;  ?>            
                    
                        </div>                                 
                      
    

                        <?php 
                        
                        if($day == "Vendredi"){                               

                            if(isset($_POST['case'])){                         

                                echo "Vous avez coché la case";                          
        
                                // On doit récupérer l'id de la semaine et le passer à un href qui nous renvera dans un controller
                                // qui va récupérer les données de cette semaine et les passer à une vue qui va les afficher 
        
                                }
                                else{
                                    echo "Vous n'avez pas coché la case";   
                                }

                        }                  
                      


                         // Récupérons les cours du jour : 

                        // on boucle sur le tableau de semaines    
                        // on vérifie que la semaine est dans le mois 
                        // On vérifie si le jour appartient à la semaine
                        // Si le jour appartient à la semaine , on récupère ses cours 


                        // Cours du groupe 1 , cours[0] correspond au cours de 8h30 , cours[1] à celui de 10h30 etc...          

                           $cours1 = [];                             

                        for($j = 0; $j < count($semaines); $j++){

                           
                            if(($day != "Samedi") && ($day != "Dimanche")){                   

                                    //var_dump($day);                            

                                 // On vérifie si le mois du jour est égale au mois de début ou au mois de fin de la semaine    
                                if(intval($date->format('m')) == intval(substr($semaines[$j]['date_debut'],3,-3)) || intval($date->format('m')) == intval(substr($semaines[$j]['date_fin'],3,-3))){

                                    // On vérifie que la distance entre le jour et le 1er jour de la semaine est inférieure ou égale à 5       
                                    if(( 0 <= ( intval($date->format('d')) - intval(substr($semaines[$j]['date_debut'],0,-6)) ) && 5 >= ( intval($date->format('d')) - intval(substr($semaines[$j]['date_debut'],0,-6)) ) ) || ( 26 <= ( intval(substr($semaines[$j]['date_debut'],0,-6)) - intval($date->format('d')) ) && 30 >= ( intval(substr($semaines[$j]['date_debut'],0,-6)) - intval($date->format('d')) ) )){

                                        $m = "m"; 
                                        $matiere = ""; 
   
                                        if($day == "Lundi"){

                                            for($t=1; $t<=5; $t++){

                                                $matiere  = $m . strval($t); 

                                                $cours1[$t-1] =  $semaines[$j]['groupe1']['lundi'][$matiere]['nom'];
                                            }

                                            var_dump($cours1[0]); 
                                           /* var_dump($cours1[1]);        
                                            var_dump($cours1[2]);                       
                                            var_dump($cours1[3]);           
                                            var_dump($cours1[4]);    */ 
                                          
                                        }
                                        if($day == "Mardi"){

                                            for($t=1; $t<=5; $t++){

                                                $matiere  = $m . strval($t); 

                                                $cours1[$t-1] =  $semaines[$j]['groupe1']['mardi'][$matiere]['nom'];
                                            }
                                            var_dump($cours1[0]); 
                                         
                                        }
                                        if($day == "Mercredi"){

                                            for($t=1; $t<=5; $t++){

                                                $matiere  = $m . strval($t); 

                                                $cours1[$t-1] =  $semaines[$j]['groupe1']['mardi'][$matiere]['nom'];
                                            }
                                            
                                            var_dump($cours1[0]);  
                                           
                                        }
                                        if($day == "Jeudi"){

                                            for($t=1; $t<=5; $t++){

                                                $matiere  = $m . strval($t); 

                                                $cours1[$t-1] =  $semaines[$j]['groupe1']['mardi'][$matiere]['nom'];
                                            }
                                            
                                            var_dump($cours1[0]);  
                                         
                                        }
                                        if($day == "Vendredi"){

                                            for($t=1; $t<=5; $t++){

                                                $matiere  = $m . strval($t); 

                                                $cours1[$t-1] =  $semaines[$j]['groupe1']['mardi'][$matiere]['nom'];
                                            }
                                            
                                            var_dump($cours1[0]);                                     
                                           
                                        }
                                    

                                             
                                    }           

                                      

                                }

                            
                            
                                }
                          

                        }

 

                    ?>        
                                   
                    </td>    
                                                        
                <?php endforeach;   ?>                                      

                </tr>

                <?php endfor;   ?>                 


         </table>     
</body>
</html>