<?php

namespace App\Controller;

use App\Calendar\Month;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PromoController extends AbstractController
{
        
      
    public $nonGroup = 9;                    

         
      /**
     * @Route("/api/add_promo", name="add_promo", methods={"POST"})   
     */                 
    public function add_promo(Request $request)  
    {
                                                          
    // Récupération  des données                  
    $data = json_decode($request->getContent(), true);
    $nom_promo =  $data['nomPromotion'];    
    $niveau_formation =  $data['niveauFormation'];  
    $debut = $data['debutSem'];  
    $fin =   $data['finSem'];                                   
    $groups =  $data['nomGroupe'];   
    $nbgroup = count($groups);   

   
    // Pour  $first_week et  $last_week le format est 'jj-mm-aa'                   
    $first_week = $data['dateDebut'];  
    $last_week =  $data['dateFin'];                                           
      
    // Les éléments de $free_weeks doivent être les noms de semaines de congés 
    $free_weeks =  $data['semConges'];                   
    
    
    $start =  intval(substr($debut,1));                           
    $end =  intval(substr($fin,1));                      
     
    $sample = json_decode(file_get_contents('sample.json'), true);  
    $init_sample = json_decode(file_get_contents('sample.json'), true);      
    $newgroup = json_decode(file_get_contents('groupe.json'), true);
    
    
    $sample[0]["nom"] = $debut;  
    $g = "groupe";                     
      
    $compter = 0; 
    // Création de la 1ère semaine                 
    for($j = 1; $j < $nbgroup; $j++){
      // $this->nonGroup correspond au nombre de propriétés d'une semaine qui ne sont pas des groupes  
      $index =  count($sample[0])  - $this->nonGroup ;           
      $index++;               
      $group  = $g . strval($index); 
         
        //dd($this->nonGroup, count($sample));     
      
      $sample[0] = array_merge($sample[0], $newgroup);       
      
      // dd( $sample[0] );    
      
      // Modifions l'index "0" en "group"              
      $sample[0][$group] = $sample[0][0];                                                     
      unset($sample[0][0]);   
      $compter++; 
         
    } 
                          
    file_put_contents("sample.json", json_encode($sample));  
                
    
    $sample  = json_decode(file_get_contents('sample.json'), true);   
    
    //dd("sample ::::",$sample);

    $sample2 =  json_decode(file_get_contents('sample.json'), true);    
    
    $nom_promo = str_replace(' ', '', $nom_promo);    
    $niveau_formation = str_replace(' ', '', $niveau_formation);

    $file = $nom_promo .  "_" . $niveau_formation . ".json";      
    
    $nextId = 1;      
    $start++;              
     
    
    for($k = $start; $k <= $end; $k++){
        
    
        $nom = "S" . strval($k);             
        $nextId++; 
            
        $sample2[0]["id"] =  $nextId;         
        $sample2[0]["nom"] = $nom;               
        $result = []; 
              
        if( $nextId == 2 ){       

        $result = array_merge($sample,$sample2);                                     

        // Création du fichier json de la promo                                                                
        file_put_contents($file, json_encode($result));        
        

        }
        else{        


          $result  = json_decode(file_get_contents( $file ), true);           
          $result2 = array_merge($result,$sample2);                            
          file_put_contents($file, json_encode($result2));        

        }
          
                                                                                      
      }                                 
                
      
        $result  = json_decode(file_get_contents( $file ), true);    
      

        // Nommons les groupes "groupe1", "groupe2" ... par défaut 

        $g = "groupe";    
        $index = 0 ;         
     
            
        for($t = 0; $t < count($result); $t++){
          
          $index = 0;  

          for($j = 0; $j < $nbgroup ; $j++){

                 
            $index++;    
            $group  = $g . strval($index);            

            $result[$t][$group]["group_name"] =  $groups[$j];  
            $result[$t][$group]["id"] =  $j+1;                            

          }

        }


        // Précisons les semaines de congés et les dates de la promotion

        for($i=0; $i<count($result); $i++){
            $result[$i]["date_debut"] = $first_week;
            $result[$i]["date_fin"] = $last_week;
            $result[$i]["nom_promotion"] = $nom_promo;
            $result[$i]["niveau_formation"] = $niveau_formation;
          for($j=0; $j<count($free_weeks); $j++){
              
            if( $result[$i]["nom"] == $free_weeks[$j]){
                $result[$i]["free"] = 1;               
            }   
                                  
               
          }

        }
               
                      
        file_put_contents($file, json_encode($result));               

        // Réinitialisation du sample       
        file_put_contents("sample.json", json_encode($init_sample));   
          
        return $this->json($result);
                
    }
}