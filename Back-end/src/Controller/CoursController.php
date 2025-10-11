<?php

namespace App\Controller;

use PhpParser\Node\Expr\Cast\String_;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CoursController extends AbstractController
{
            
             
     /**
     * @Route("/api/planning/{promotion}/ajouterCours", name="api_planning_ajouterCours", methods={"POST"})        
     */
    public function ajouterCours(Request $request, String $promotion)        
    {
        

        // Récupération des donnnées    
        $data = json_decode($request->getContent(), true);
        $nom = $data['nom'];  
        $type = $data["type"];      
        $prof = $data["prof"];    
        $jour = $data["jour"];  
        $heure =  $data["heure"]; 
        $semaine = $data["semaine"];      
        $GROUPS =  $data['groups'];       
        
      
        $t = 0;    
            
      // Déterminons le nom de la promotion et le niveau de formation    
      $suivant = 0; 

      $nom_promotion = "";     

      $niveau_formation = "";  

      for($i=0; $i<strlen($promotion);$i++){

          if(($promotion[$i] != "_") && ($suivant == 0) ){
              $nom_promotion .=  $promotion[$i]; 

          } else{
              if( $promotion[$i] == "_"){
                $suivant++;
              } else {
                  if( $suivant != 0 ){
                      $niveau_formation .= $promotion[$i];

                  }

              }

          }
      }


    $nom_promotion = str_replace(' ', '', $nom_promotion);    
    $niveau_formation = str_replace(' ', '', $niveau_formation);

    $fichier = $nom_promotion . "_" . $niveau_formation . ".json";                   
                             
    $semaines = json_decode(file_get_contents($fichier), true); 
       
       
       $g = "groupe"; 
       $next  = 0;              
        
    
       for($t = 0; $t < count($GROUPS); $t++){     
         
         $next++;
         
         $g = "groupe"  . strval($next);    
        
        for($j = 0; $j < count($semaines); $j++){   
               
          if(($semaines[$j]["nom"] == $semaine)){  
            
       
            if( $semaines[$j][$g]["group_name"] == $GROUPS[$t] ){ 

              $semaines[$j][$g][$jour][$heure]["nom"] = $nom;
              $semaines[$j][$g][$jour][$heure]["type"] = $type;
              $semaines[$j][$g][$jour][$heure]["profs"] = $prof;
              

            }

                     
          }  
                                                                           
      
       } 
      
    }   
        
     // Mise à jour du fichier json de la promotion       
     file_put_contents($fichier, json_encode($semaines));    
      
            
      return $this->json($semaines);                                    
        
    }

                                      
     /**
     * @Route("/api/planning/fixerCours/{id}", name="api_planning_fixerCours", methods={"POST"},requirements={"id"="\d+"})            
     */
    public function fixerCours(Request $request)             
    { 
                                                                       
      $id =  $request->get("id");                     
      $group = $request->get("group");      
      $jour = $request->get("jour");  
      $heure =  $request->get("heure");     
         
      $semaines = json_decode(file_get_contents('semaines.json'), true);      
                  
      for($j = 0; $j < count($semaines); $j++){

                   
        if(($semaines[$j]["id"] == $id)){              

              
          if($semaines[$j][$group][$jour][$heure]["fixed"] == 0){      

            $semaines[$j][$group][$jour][$heure]["fixed"] = 1; 

          }else{
             
            $semaines[$j][$group][$jour][$heure]["fixed"] = 0; 
                  
          }  

                            
        }                   
                                                                      
    }   
    
    
      // Mise à jour du fichier json de la promotion    

      file_put_contents('semaines.json',json_encode($semaines));                               
                           
      return $this->json($semaines);                     
          
    }


    
     /**
     * @Route("/api/planning/copierCours", name="api_planning_copierCours", methods={"POST"})        
     */
    public function copy(Request $request)        
    {
      
      // Récupération des données 
       
        $nom = $request->get("nom");             
        $type = $request->get("type");   
        $prof = $request->get("prof");
        $jour = $request->get("jour");
        $heure =  $request->get("heure");      
        $group = $request->get("group");
        $debut = $request->get("debut");      
        $fin = $request->get("fin");      
        $heure_fin =  $request->get("heure_fin");   

        
        $dif = intval(substr($heure_fin,-1,1)) - intval(substr($heure,-1,1));        
        $start = intval(substr($heure,-1,1));
       
        $end =  intval(substr($heure_fin,-1,1));             

        $semaines = json_decode(file_get_contents('semaines.json'), true); 
        
        $m = "m";          
        $h = "";                                         

        if($dif > 0){
          
          $start++;   
                 
        for($j = 0; $j < count($semaines); $j++){

                   
          if(($semaines[$j]["date_debut"] == $debut) && ($semaines[$j]["date_fin"] == $fin)){

                 
            for($k= $start; $k <= $end; $k++){

              $h  = $m . strval($k); 
              

              $semaines[$j][$group][$jour][$h]["nom"] = $nom;
              $semaines[$j][$group][$jour][$h]["type"] = $type;
              $semaines[$j][$group][$jour][$h]["profs"] = $prof;      

            }
            
                     
          }  
                                                                        
      }       
   
        }
        else{       

             
          $start--;   
                 
          for($j = 0; $j < count($semaines); $j++){
  
                     
            if(($semaines[$j]["date_debut"] == $debut) && ($semaines[$j]["date_fin"] == $fin)){
  
                   
              for($k= $start; $k >= $end; $k--){
  
                $h  = $m . strval($k);    
                           
                $semaines[$j][$group][$jour][$h]["nom"] = $nom;
                $semaines[$j][$group][$jour][$h]["type"] = $type;
                $semaines[$j][$group][$jour][$h]["profs"] = $prof;      
  
              }
              
                       
            }     
                                                                          
        }       

    
        }
    

     // Mise à jour du fichier json de la promotion      
      file_put_contents('semaines.json', json_encode($semaines));    
               
      return $this->json($semaines);  
         
    }
        
      /**
     * @Route("/api/planning/{promotion}/supprimerCours", name="api_planning_supprimerCours", methods={"POST"})                
     */
    public function delete(Request $request, String $promotion)        
    {                
      

      // Récupération des données 
      
      $data = json_decode($request->getContent(), true);     
      $nom = $data['nom'];  
      $type = $data["type"];            
      $prof = $data["prof"];       
      $jour = $data["jour"];  
      $heure =  $data["heure"]; 
      $semaine = $data["semaine"];                
      $GROUPS =  $data['groups'];      
  
      
      $t = 0;    
      
     // Déterminons le nom de la promotion et le niveau de formation          
     $suivant = 0; 

     $nom_promotion = "";     

     $niveau_formation = "";  

     for($i=0; $i<strlen($promotion);$i++){

         if(($promotion[$i] != "_") &&($suivant == 0) ){
             $nom_promotion .=  $promotion[$i]; 

         } else{
             if( $promotion[$i] == "_"){
               $suivant++;
             } else {
                 if( $suivant != 0 ){
                     $niveau_formation .= $promotion[$i];

                 }

             }

         }
     }       
     
    $nom_promotion = str_replace(' ', '', $nom_promotion);    
    $niveau_formation = str_replace(' ', '', $niveau_formation);

    $fichier = $nom_promotion . "_" . $niveau_formation . ".json"; 

    $semaines = json_decode(file_get_contents($fichier), true); 
     
    $g = "groupe"; 
    $next  = 0;                
      
     // Supprimons le cours  
   
      for($t = 0; $t < count($GROUPS); $t++){     
                                  
        $next++;
        $g = $g . strval($next);   

       for($j = 0; $j < count($semaines); $j++){  
              
         if(($semaines[$j]["nom"] == $semaine)){
           if( $semaines[$j][$g]["group_name"] == $GROUPS[$t] ){         
               
             
            if( ($semaines[$j][$g][$jour][$heure]["nom"] == $nom) && ($semaines[$j][$g][$jour][$heure]["type"] == $type) &&  ($semaines[$j][$g][$jour][$heure]["profs"] == $prof)){

              $semaines[$j][$g][$jour][$heure]["nom"] = "";
              $semaines[$j][$g][$jour][$heure]["type"] = "";             
              $semaines[$j][$g][$jour][$heure]["profs"] = "";
    
            }
         
    
           }

                        
         }  
                                                                          
     }    
                     
   }        
                     
     
   // Mise à jour du fichier json de la promotion      

     file_put_contents($fichier, json_encode($semaines));    
            
     return $this->json($semaines);                                            
        
    }
        

           
}
         