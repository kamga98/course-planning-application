<?php

namespace App\Controller;


use Calendar\Events;
use App\Calendar\Month;
use PhpParser\Node\Expr\Cast\String_;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
            

header("Access-Control-Allow-Origin: *");                

class PlanningController extends AbstractController                 
{
   
    private $BLOC = [];      
                     

    public function setBloc($bloc){

      $this->BLOC = $bloc; 
                 
    }

    public function getBloc(){

      return  $this->BLOC;          
                 
    }

    public $nonGroup = 9;                      
  
     /**  
     * @Route("/api/init", name="api_init", methods={"POST"})            
     */
    public function init(Request $request)        
    {
  

     // Récupération des donnnées                      
    $nom = $request->get("nom");
    $debut =  $request->get("debut"); 
    $fin =  $request->get("fin");  
    $nbgroup =  intval($request->get("nb_group"));    
    $start =  intval(substr($debut,1));                           
    $end =  intval(substr($fin,1));                         
  
    
    $sample = json_decode(file_get_contents('sample.json'), true);  
    $init_sample = json_decode(file_get_contents('sample.json'), true);      
    $newgroup = json_decode(file_get_contents('groupe.json'), true);
    
    $sample[0]["nom"] = $debut;     
    $g = "groupe";                        
        
    // Création de la 1ère semaine          
    for($j = 1; $j < $nbgroup; $j++){
      
      //  $this->nonGroup  correspond au nombre de propriétés d'une semaine qui ne sont pas des groupes  
      $index =  count($sample[0])  - $this->nonGroup ;              
      $index++;    
      $group  = $g . strval($index); 
      $sample[0] = array_merge($sample[0], $newgroup);       
      
     
      // Modifions l'index "0" en "group"                  
      $sample[0][$group] = $sample[0][0];                                                     
      unset($sample[0][0]);     
          
    }     
                             
    file_put_contents("sample.json", json_encode($sample));  
                
    
    $sample  = json_decode(file_get_contents('sample.json'), true);           
    $sample2 =  json_decode(file_get_contents('sample.json'), true);    
    
    $file = $nom . ".json";    
    
                
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

        // Création du fichier json de la promotion                                                                 
        file_put_contents($file, json_encode($result));        
        

        }
        else{        


          $result  = json_decode(file_get_contents( $file ), true);           
          $result2 = array_merge($result,$sample2);                            
          file_put_contents($file, json_encode($result2));        

        }
          
                                                                                      
      }                             
                            
      $result  = json_decode(file_get_contents( $file ), true);                                   
      file_put_contents($file, json_encode($result));               

      // Réinitialisation du sample       
      file_put_contents("sample.json", json_encode($init_sample));                          
    
      return $this->json($result);                                                                  
               
               
                                           
    }        

     
    /**
     * @Route("/api/week/{id}", name="api_getWeek", methods={"GET"},requirements={"id"="\d+"})           
     */
    public function index($id)                                                                   
    {              

        $semaines = json_decode(file_get_contents('semaines.json'), true);  
                           
        for($j = 0; $j < count($semaines); $j++){        

        
            if($semaines[$j]["id"] == $id){

                return $this->json($semaines[$j]);
            }   

        }
  

      return new Response("Aucun cours n'a encore été programmé pour cette semaine");                        
        
    }   
    
    /**
     * @Route("/api/planning/deleteGroup", name="api_planning_deleteGroup", methods={"POST"})            
     */
    public function delete_group(Request $request)        
    {
                                                               
                
      $nom_group = $request->get("nom_group");   
        
      $semaines = json_decode(file_get_contents('semaines.json'), true); 
        
      $g = "groupe";           
           
      //  $this->nonGroup correspond au nombre de propriétés d'une semaine qui ne sont pas des groupes  
      $index =  count($semaines[0])  -  $this->nonGroup  ;             
       
      for($j = 0; $j < count($semaines); $j++){               
  

         for($i=1; $i<= $index; $i++){

            $g = "groupe" . strval($i);
   
            if($semaines[$j][$g]["group_name"] ==  $nom_group ){
       
                unset($semaines[$j][$g]);
                $semaines = array_merge($semaines);            


            }

         }
                                                                                     
      }                  
                                                                                                     
       
     file_put_contents('semaines.json',json_encode($semaines));                               
                         
    return $this->json($semaines);                           
   
    }

                              
     /**
     * @Route("/api/planning/addWeek", name="api_post_addWeek", methods={"POST"})            
     */
    public function addWeek(Request $request)        
    {
           
                                
    
      $nom = $request->get("nom");        
      $debut = $request->get("debut");      
      $fin = $request->get("fin");             
         
      $semaines = json_decode(file_get_contents('semaines.json'), true);                       
      $copy = json_decode(file_get_contents('semaines.json'), true);  
      $semaine  = json_decode(file_get_contents('semaine.json'), true);    
   
      $semaine[0]['id'] =  count($semaines);                                                         
      $semaine[0]['nom'] = $nom;                                                                   
      $semaine[0]['date_debut'] =  $debut;           
      $semaine[0]['date_fin'] =  $fin;                                                        
         
      $fusion = array_merge($copy,$semaine);             

      file_put_contents('semaines.json',json_encode($fusion));                               
                              
      return $this->json($fusion);                      
      
          
    }          
     


    /**
     * @Route("/api/planning/newGroup", name="api_planning_newGroup", methods={"POST"})            
     */
    public function add_group(Request $request)        
    {
           
    
      // Récupération du nom de la promotion 
      $promo = $request->get("nom_promo");      
           
      // En fonction de la promotion choisie, on modifiera soit semaines1.json ou semaines2.json ...   
      // pour l'instant faisons les tests uniquement avec semaines.json  
          
      $semaines = json_decode(file_get_contents('semaines.json'), true); 
      $newgroup = json_decode(file_get_contents('groupe.json'), true);            

      $g = "groupe";           
           
      //  $this->nonGroup  correspond au nombre de propriétés d'une semaine qui ne sont pas des groupes  
      $index =  count($semaines[0])  -  $this->nonGroup  ;           
       
      $group  = $g . strval($index);                            
     
      for($j = 0; $j < count($semaines); $j++){               
     
          $semaines[$j] = array_merge($semaines[$j], $newgroup); 
    
          // Modification du nom de l'index "0" en "group"   
          $semaines[$j][$group] = $semaines[$j][0];                                                     
          unset($semaines[$j][0]);                   

                                                                                     
      } 
                           
                                         
    file_put_contents('semaines.json',json_encode($semaines));                               
                            
    return $this->json($semaines);                           
    
    }   


                                     
     /**
     * @Route("/api/planning/fullWeek/{id}", name="api_planning_fullWeek", methods={"GET"},requirements={"id"="\d+"})            
     */
    public function full_week($id)        
    {                       
                         
      $semaines = json_decode(file_get_contents('semaines.json'), true); 
               
      for($j = 0; $j < count($semaines); $j++){     

                   
        if(($semaines[$j]["id"] == $id)){

               
          if($semaines[$j]["full"]){

            $semaines[$j]["full"] = 0;         

          }
          else{
            $semaines[$j]["full"] = 1; 
          }
          
                   
        }                   
                                                                      
    }     
       
      file_put_contents('semaines.json',json_encode($semaines));                               
                         
      return $this->json($semaines);                     
         
          
    }

               
                                     
     /**
     * @Route("/api/planning/freeWeek/{id}", name="api_planning_freeWeek", methods={"GET"},requirements={"id"="\d+"})            
     */
    public function free_week($id)        
    {       
                                      
   
      $semaines = json_decode(file_get_contents('semaines.json'), true); 
               
  
      for($j = 0; $j < count($semaines); $j++){

                   
        if(($semaines[$j]["id"] == $id) ){

               
          if($semaines[$j]["free"]){    

            $semaines[$j]["free"] = 0;         

          }
          else{
            $semaines[$j]["free"] = 1; 
          }
          
                   
        }      
                                                                      
    }     
                 
      file_put_contents('semaines.json',json_encode($semaines));                               
                            
      return $this->json($semaines);                     
           
        
    }

              
     /**  
     * @Route("/api/planning/visibleWeek/{id}", name="api_planning_visibleWeek", methods={"POST"}, requirements={"id"="\d+"})            
     */
    public function visible($id)                
    {
                              
      $semaines = json_decode(file_get_contents('semaines.json'), true); 
               
      for($j = 0; $j < count($semaines); $j++){

                   
        if(($semaines[$j]["id"] == $id)){

               
          if($semaines[$j]["visible"]){

            $semaines[$j]["visible"] = 0;         

          }
          else{
            $semaines[$j]["visible"] = 1;      
          }
          
                   
        }      
                                                                      
    }     
   
            
      file_put_contents('semaines.json',json_encode($semaines));                               
       
                       
      return $this->json($semaines);                     
      
          
        
    }   

  
     /**
     * @Route("/api/planning/allWeeks", name="api_planning_allWeeks", methods={"GET"})            
     */
    public function all(Request $request)              
    {
                                  
      $semaines = json_decode(file_get_contents('semaines.json'), true); 
              
                                                    
      return $this->json($semaines);    
                        
    }           

      

     /**           
     * @Route("/api/planning/bloc/{promotion}/{nom}", name="api_planning_bloc", methods={"GET"})            
     */
    public function bloc(String $nom, String $promotion)    
    {
         
        /*   

          - Récupérer toutes les semaines :   $semaines = json_decode(file_get_contents('semaines.json'), true);  
          - Récupérer le nom de la première semaine en paramètre 
          - Récupérer les 4 semaines de la page courante 
          - Lire bloc.json 
          - Remplir chaque semaine du bloc avec les données correspondantes
          - Retourner bloc.json 
          
                    
        */         
   
        // Détermination du nom de la promotion et du niveau de formation    
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

      $bloc = json_decode(file_get_contents('bloc.json'), true);                 
    
      $check = 0;                              
      $index = 0; 
         
      // Nombre de groupes                 
      $nbre =  count($semaines[0])  -  $this->nonGroup  ;     
       
     
      // Récupération des semaines du bloc 
      for($j = 0; $j < count($semaines); $j++){

        if( $nom == $semaines[$j]["nom"] ){    
          
          $check = $j;
          $index = 1; 
        
          $bloc[0]["id"] = $semaines[$j]["id"];   
          $bloc[0]["nom"] = $semaines[$j]["nom"];
          $bloc[0]["date_debut"] = $semaines[$j]["date_debut"];
          $bloc[0]["date_fin"] = $semaines[$j]["date_fin"];
          $bloc[0]["full"] = $semaines[$j]["full"];
          $bloc[0]["visible"] = $semaines[$j]["visible"];
          $bloc[0]["nom_promotion"] = $semaines[$j]["nom_promotion"];
          $bloc[0]["niveau_formation"] = $semaines[$j]["niveau_formation"];

          // boucle sur les groupes 

          $m = "m";             
          $g = "groupe";      

          for($k = 1; $k <= 5; $k++){

           
            $heure = $m . strval($k); 
            
              // $nbre correspond au nombre de groupe

            for($t = 1; $t <= $nbre; $t++){
                
              $group  = $g . strval($t);   
              
              $heure = $m . strval($k); 

               // Pour le bloc                            
              $heur = 0; 
              $groupe = 0; 
              
              // Si $heure = "m1" on aura $heur = 0 etc ... 
              $heur = intval($heure[1]) - 1 ;   
              
              // Si $group = "groupe1" on aura $groupe = 0 etc ... 
              $groupe = intval($group[6]) - 1;                                          
              
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              // le 2e 0 correspond à lundi   
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["lundi"][$heure]["nom"];
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["lundi"][$heure]["type"];
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["lundi"][$heure]["profs"];
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["lundi"][$heure]["fixed"];
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "lundi";      
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;     

          
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["mardi"][$heure]["nom"];
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["mardi"][$heure]["type"];
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["mardi"][$heure]["profs"];
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["mardi"][$heure]["fixed"];
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mardi";      
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;   
  
   
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["mercredi"][$heure]["nom"];
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["mercredi"][$heure]["type"];
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["mercredi"][$heure]["profs"];
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["mercredi"][$heure]["fixed"];
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mercredi";      
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;

   
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["jeudi"][$heure]["nom"];
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["jeudi"][$heure]["type"];
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["jeudi"][$heure]["profs"];
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["jeudi"][$heure]["fixed"];
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "jeudi";      
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;


              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["vendredi"][$heure]["nom"];
              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["vendredi"][$heure]["type"];
              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["vendredi"][$heure]["profs"];
              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["vendredi"][$heure]["fixed"];
              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "vendredi";      
              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;      

    

         
            }


          }     
        

        }
       

        // Pour les 3 autres semaines du bloc 
        if( ($check < $j ) && ($j <= $check + 3 )){                    


          $bloc[$index]["id"] = $semaines[$j]["id"];              
          $bloc[$index]["nom"] = $semaines[$j]["nom"];
          $bloc[$index]["date_debut"] = $semaines[$j]["date_debut"];
          $bloc[$index]["date_fin"] = $semaines[$j]["date_fin"];
          $bloc[$index]["full"] = $semaines[$j]["full"];
          $bloc[$index]["visible"] = $semaines[$j]["visible"];
          $bloc[$index]["nom_promotion"] = $semaines[$j]["nom_promotion"];  
          $bloc[$index]["niveau_formation"] = $semaines[$j]["niveau_formation"];         

          // boucle sur les groupes 

          $m = "m";
          $g = "groupe";    

          for($k = 1; $k <= 5; $k++){
     
            $heure = $m . strval($k); 
                
            for($t = 1; $t <= $nbre; $t++){  
              $group  = $g . strval($t);   
    
              $heure = $m . strval($k); 

              // Pour le bloc 
              $heur = 0; 
              $groupe = 0; 

              // Si $heure = "m1" on aura $heur = 0 etc ... 
              $heur = intval($heure[1]) - 1 ;   
              
              // Si $group = "groupe1" on aura $groupe = 0 etc ... 
              $groupe = intval($group[6]) - 1 ;                     
                         
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];
        
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["lundi"][$heure]["nom"];
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["lundi"][$heure]["type"];
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["lundi"][$heure]["profs"];
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["lundi"][$heure]["fixed"];
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "lundi";      
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;



              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["mardi"][$heure]["nom"];
              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["mardi"][$heure]["type"];
              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["mardi"][$heure]["profs"];
              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["mardi"][$heure]["fixed"];
              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mardi";      
              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;   


   
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["mercredi"][$heure]["nom"];
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["mercredi"][$heure]["type"];
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["mercredi"][$heure]["profs"];
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["mercredi"][$heure]["fixed"];
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mercredi";      
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;



              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["jeudi"][$heure]["nom"];
              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["jeudi"][$heure]["type"];
              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["jeudi"][$heure]["profs"];
              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["jeudi"][$heure]["fixed"];
              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "jeudi";      
              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;

             
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["vendredi"][$heure]["nom"];
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["vendredi"][$heure]["type"];
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["vendredi"][$heure]["profs"];
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["vendredi"][$heure]["fixed"];
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "vendredi";      
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;      
   
           
            }


          }

          $index++; 

        }                  
   
      } 

        $this->setBloc($bloc);                                  
    
        return $bloc;                                                           
         
                        
    }

    
     /**   
     * @Route("/api/planning/nextBloc/{promotion}/{nom}", name="api_planning_nextBloc", methods={"GET"})            
     */
    public function nextBloc(String $nom, String $promotion)                   
    {   
  
    
    $bloc = $this->bloc($nom,$promotion);          
      
    
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

      // On doit remplacer la 4e semaine du bloc par celle qui la suit dans semaines.json
        
      // Détermination du nom de la semaine suivante : 

     $next_week = "S" . strval(intval(substr($nom,1)) + 4);  
    
     // Nombre de groupes                     
     $nbre =  count($semaines[0])  -  $this->nonGroup  ;    
     
     // Les semaines 0, 1, 2 du bloc deviennent respectivement les semaines 1, 2 et 3 et la 3 devient la next 

     for($i = 0; $i <= 2; $i++){
     
      $bloc[$i]["id"] = $bloc[$i+1]["id"];   
      $bloc[$i]["nom"] =  $bloc[$i+1]["nom"];
      $bloc[$i]["date_debut"] =  $bloc[$i+1]["date_debut"];
      $bloc[$i]["date_fin"] = $bloc[$i+1]["date_fin"];
      $bloc[$i]["full"] =  $bloc[$i+1]["full"];
      $bloc[$i]["visible"] =  $bloc[$i+1]["visible"] ; 
      $bloc[$i]["nom_promotion"] =  $bloc[$i+1]["nom_promotion"] ; 
      $bloc[$i]["niveau_formation"] =  $bloc[$i+1]["niveau_formation"] ;      

      // boucle sur les groupes 
        
      $m = "m";             
      $g = "groupe";                        

      for($k = 1; $k <= 5; $k++){
   
        $heure = $m . strval($k); 
           
        for($t = 1; $t <= $nbre; $t++){
            
          $group  = $g . strval($t);   

          $heure = $m . strval($k); 

          // Pour le bloc                             
          $heur = 0; 
          $groupe = 0; 
          
          // Si $heure = "m1" on aura $heur = 0 etc ... 
          $heur = intval($heure[1]) - 1 ;   
          
          // Si $group = "groupe1" on aura $groupe = 0 etc ... 
          $groupe = intval($group[6]) - 1;                                          
          

          // le 2e 0 correspond à lundi   
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $bloc[$i+1]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"];
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $bloc[$i+1]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] ;
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $bloc[$i+1]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] ;
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] =  $bloc[$i+1]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"];
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] =  $bloc[$i+1]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] ; 
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "lundi";      
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;     

          
          
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $bloc[$i+1]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"];
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $bloc[$i+1]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] ;
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $bloc[$i+1]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] ;
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] =  $bloc[$i+1]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"];
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] =  $bloc[$i+1]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] ; 
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mardi";      
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;   

     
  
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $bloc[$i+1]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"];
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $bloc[$i+1]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] ;
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $bloc[$i+1]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] ;
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] =  $bloc[$i+1]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"];
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] =  $bloc[$i+1]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] ; 
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mercredi";      
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;


          
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $bloc[$i+1]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"];
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $bloc[$i+1]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] ;
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $bloc[$i+1]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] ;
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] =  $bloc[$i+1]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"];
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] =  $bloc[$i+1]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] ; 
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "jeudi";      
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;    
       
  
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $bloc[$i+1]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"];
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $bloc[$i+1]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] ;
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $bloc[$i+1]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] ;
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] =  $bloc[$i+1]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"];
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] =  $bloc[$i+1]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] ; 
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "vendredi";      
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;      


        }      


      }     
    

     } 

               
     for($j = 0; $j < count($semaines); $j++){
    
      if( $next_week == $semaines[$j]["nom"] ){    
          
    
        $bloc[3]["id"] = $semaines[$j]["id"];   
        $bloc[3]["nom"] = $semaines[$j]["nom"];
        $bloc[3]["date_debut"] = $semaines[$j]["date_debut"];
        $bloc[3]["date_fin"] = $semaines[$j]["date_fin"];
        $bloc[3]["full"] = $semaines[$j]["full"];
        $bloc[3]["visible"] = $semaines[$j]["visible"];
        $bloc[3]["nom_promotion"] =  $semaines[$j]["nom_promotion"] ; 
        $bloc[3]["niveau_formation"] =  $semaines[$j]["niveau_formation"] ;     

        // boucle sur les groupes 

        $m = "m";             
        $g = "groupe";      

        for($k = 1; $k <= 5; $k++){

         
          $heure = $m . strval($k); 
          
          
          for($t = 1; $t <= $nbre; $t++){
              
            $group  = $g . strval($t);   

            $heure = $m . strval($k); 

            // Pour le bloc                             
            $heur = 0; 
            $groupe = 0; 
            
            // Si $heure = "m1" on aura $heur = 0 etc ... 
            $heur = intval($heure[1]) - 1 ;   
            
            // Si $group = "groupe1" on aura $groupe = 0 etc ... 
            $groupe = intval($group[6]) - 1;                                          
            

            // le 2e 0 correspond à lundi   
            $bloc[3]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["lundi"][$heure]["nom"];
            $bloc[3]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["lundi"][$heure]["type"];
            $bloc[3]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["lundi"][$heure]["profs"];
            $bloc[3]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["lundi"][$heure]["fixed"];
            $bloc[3]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
            $bloc[3]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "lundi";      
            $bloc[3]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;     

        
            
            $bloc[3]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["mardi"][$heure]["nom"];
            $bloc[3]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["mardi"][$heure]["type"];
            $bloc[3]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["mardi"][$heure]["profs"];
            $bloc[3]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["mardi"][$heure]["fixed"];
            $bloc[3]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
            $bloc[3]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mardi";      
            $bloc[3]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;   

 
    
            $bloc[3]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["mercredi"][$heure]["nom"];
            $bloc[3]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["mercredi"][$heure]["type"];
            $bloc[3]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["mercredi"][$heure]["profs"];
            $bloc[3]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["mercredi"][$heure]["fixed"];
            $bloc[3]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
            $bloc[3]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mercredi";      
            $bloc[3]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;

 
            
            $bloc[3]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["jeudi"][$heure]["nom"];
            $bloc[3]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["jeudi"][$heure]["type"];
            $bloc[3]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["jeudi"][$heure]["profs"];
            $bloc[3]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["jeudi"][$heure]["fixed"];
            $bloc[3]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
            $bloc[3]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "jeudi";      
            $bloc[3]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;


              
            $bloc[3]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["vendredi"][$heure]["nom"];
            $bloc[3]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["vendredi"][$heure]["type"];
            $bloc[3]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["vendredi"][$heure]["profs"];
            $bloc[3]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["vendredi"][$heure]["fixed"];
            $bloc[3]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
            $bloc[3]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "vendredi";      
            $bloc[3]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;      

       
          }


        }     
      
      }


     }

     return $this->json($bloc);                              
               
            
    }   
    
    

     /**   
     * @Route("/api/planning/previousBloc/{promotion}/{nom}", name="api_planning_previousBloc", methods={"GET"})            
     */
    public function previous_bloc(String $nom, String $promotion)                                               
    {              
     
            
     $bloc = $this->bloc($nom,$promotion);          
         
      
      // Détermination du nom de la semaine suivante : 
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

     $next_week = "S" . strval(intval(substr($nom,1)) - 1);              
                            
     // Nombre de groupes                 
     $nbre =  count($semaines[0])  -  $this->nonGroup  ;     
 
    // Les semaines 1, 2 et 3 du bloc deviennent respectivement les semaines 0, 1 et 2 

     for($i = 3; $i >= 1; $i--){                             
   
              
      $bloc[$i]["id"] = $bloc[$i-1]["id"];   
      $bloc[$i]["nom"] =  $bloc[$i-1]["nom"];
      $bloc[$i]["date_debut"] =  $bloc[$i-1]["date_debut"];
      $bloc[$i]["date_fin"] = $bloc[$i-1]["date_fin"];
      $bloc[$i]["full"] =  $bloc[$i-1]["full"];
      $bloc[$i]["visible"] =  $bloc[$i-1]["visible"] ;     
      $bloc[$i]["nom_promotion"] =  $bloc[$i-1]["nom_promotion"] ; 
      $bloc[$i]["niveau_formation"] =  $bloc[$i-1]["niveau_formation"] ;      

      // boucle sur les groupes     
        
      $m = "m";             
      $g = "groupe";                        

      for($k = 1; $k <= 5; $k++){

       
        $heure = $m . strval($k); 
        
        
        for($t = 1; $t <= $nbre; $t++){
            
          $group  = $g . strval($t);   

          $heure = $m . strval($k); 

          // Pour le bloc                             
          $heur = 0; 
          $groupe = 0; 
          
          // Si $heure = "m1" on aura $heur = 0 etc ... 
          $heur = intval($heure[1]) - 1 ;   
          
          // Si $group = "groupe1" on aura $groupe = 0 etc ... 
          $groupe = intval($group[6]) - 1;                                          
          
         

          // le 2e 0 correspond à lundi   
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $bloc[$i-1]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"];
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $bloc[$i-1]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] ;
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $bloc[$i-1]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] ;
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] =  $bloc[$i-1]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"];
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] =  $bloc[$i-1]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] ; 
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "lundi";      
          $bloc[$i]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;     

          
          
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $bloc[$i-1]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"];
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $bloc[$i-1]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] ;
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $bloc[$i-1]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] ;
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] =  $bloc[$i-1]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"];
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] =  $bloc[$i-1]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] ; 
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mardi";      
          $bloc[$i]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;   

     
  
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $bloc[$i-1]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"];
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $bloc[$i-1]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] ;
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $bloc[$i-1]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] ;
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] =  $bloc[$i-1]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"];
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] =  $bloc[$i-1]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] ; 
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mercredi";      
          $bloc[$i]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;


          
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $bloc[$i-1]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"];
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $bloc[$i-1]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] ;
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $bloc[$i-1]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] ;
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] =  $bloc[$i-1]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"];
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] =  $bloc[$i-1]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] ; 
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "jeudi";      
          $bloc[$i]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;    
       
  
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $bloc[$i-1]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"];
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $bloc[$i-1]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] ;
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $bloc[$i-1]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] ;
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] =  $bloc[$i-1]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"];
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] =  $bloc[$i-1]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] ; 
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "vendredi";      
          $bloc[$i]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;      

                             
        }      

          
      }     
    

     }                   


     for($j = 0; $j < count($semaines); $j++){

           
      if( $next_week == $semaines[$j]["nom"] ){    
          
    
        $bloc[0]["id"] = $semaines[$j]["id"];   
        $bloc[0]["nom"] = $semaines[$j]["nom"];
        $bloc[0]["date_debut"] = $semaines[$j]["date_debut"];
        $bloc[0]["date_fin"] = $semaines[$j]["date_fin"];
        $bloc[0]["full"] = $semaines[$j]["full"];
        $bloc[0]["visible"] = $semaines[$j]["visible"];
        $bloc[0]["nom_promotion"] =  $semaines[$j]["nom_promotion"] ; 
        $bloc[0]["niveau_formation"] = $semaines[$j]["niveau_formation"] ;     

        // boucle sur les groupes 

        $m = "m";             
        $g = "groupe";      

        for($k = 1; $k <= 5; $k++){

         
          $heure = $m . strval($k); 
          
          
          for($t = 1; $t <= $nbre; $t++){
              
            $group  = $g . strval($t);   

            $heure = $m . strval($k); 

            // Pour le bloc                             
            $heur = 0; 
            $groupe = 0; 
            
            // Si $heure = "m1" on aura $heur = 0 etc ... 
            $heur = intval($heure[1]) - 1 ;   
            
            // Si $group = "groupe1" on aura $groupe = 0 etc ... 
            $groupe = intval($group[6]) - 1;                                          
            

            // le 2e 0 correspond à lundi   
            $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["lundi"][$heure]["nom"];
            $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["lundi"][$heure]["type"];
            $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["lundi"][$heure]["profs"];
            $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["lundi"][$heure]["fixed"];
            $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
            $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "lundi";      
            $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;     

        
            
            $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["mardi"][$heure]["nom"];
            $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["mardi"][$heure]["type"];
            $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["mardi"][$heure]["profs"];
            $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["mardi"][$heure]["fixed"];
            $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
            $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mardi";      
            $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;   

 
    
            $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["mercredi"][$heure]["nom"];
            $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["mercredi"][$heure]["type"];
            $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["mercredi"][$heure]["profs"];
            $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["mercredi"][$heure]["fixed"];
            $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
            $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mercredi";      
            $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;

 
            
            $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["jeudi"][$heure]["nom"];
            $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["jeudi"][$heure]["type"];
            $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["jeudi"][$heure]["profs"];
            $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["jeudi"][$heure]["fixed"];
            $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
            $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "jeudi";      
            $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;


              
            $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["vendredi"][$heure]["nom"];
            $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["vendredi"][$heure]["type"];
            $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["vendredi"][$heure]["profs"];
            $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["vendredi"][$heure]["fixed"];
            $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
            $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "vendredi";      
            $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;      

      
       
          }


        }     
      

      }


     }

   
     return $this->json($bloc);                              
               
          
           
    }  
      
                            
            
    /**
     * @Route("/api/nom_group", name="nom_group", methods={"POST"})             
     */
    public function nom_group(Request $request)     
    {         

        $nompro = $request->get("nom_promotion");        
        $group = $request->get("groupe");      
        $nomgroup = $request->get("nom_groupe");                     
        $files = $nompro . ".json";    
        $semaines = json_decode(file_get_contents($files), true);
        $nbregroup =  count($semaines[0])  -  $this->nonGroup ;                 

        $g = "groupe";                         
          
        $index = 0;     

        for($j = 0; $j < count($semaines); $j++){

                  
            for($k=0; $k < $nbregroup; $k++){
                
                $index++;             
   
                $nextgroup = $g . strval($index);     
                                                  
                if($nextgroup == $group){    
                           
                    $semaines[$j][$nextgroup]["group_name"] = $nomgroup;             
                                                     
                }          
                           
   
            }


        }       
                    
        file_put_contents($files, json_encode($semaines));    
                
        return $this->json($semaines);   

    }


         
    /**
     * @Route("/api/conges", name="api_conges", methods={"POST"})             
     */
    public function conges(Request $request)           
    {         
    
        $nompro = $request->get("nom_promo");                          
        $files = $nompro . ".json";      
        $semaines = json_decode(file_get_contents($files), true);
        $conges = ["S38", "S40"];   
   
        for($j = 0; $j < count($semaines); $j++){

            
          for($t = 0; $t < count($conges); $t++){

            if( $semaines[$j]["nom"] == $conges[$t]){     

              $semaines[$j]["free"] = 1; 

            }
      
          } 


        }       
              
            
        file_put_contents($files, json_encode($semaines));    
      
            
        return $this->json($semaines);   

      
    }



    
     /**           
     * @Route("/api/planning/monBloc/{promotion}/{nom}", name="api_planning_bloc", methods={"GET"})            
     */
    public function monBloc(String $nom, String $promotion)    
    {
         
       
        /*   

          - Récupérer toutes les semaines :   $semaines = json_decode(file_get_contents('semaines.json'), true);  
          - Récupérer le nom de la première semaine en paramètre 
          - Récupérer les 4 semaines de la page courante 
          - Lire bloc.json 
          - Remplir chaque semaine du bloc avec les données correspondantes
          - Retourner bloc.json 
          
                    
        */         
   
       

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
      $bloc = json_decode(file_get_contents('bloc.json'), true);       
        
      //$nom = $request->get("nom");            
    
      $check = 0;                                         
      $index = 0; 
         
      // Nombre de groupes                 
      $nbre =  count($semaines[0])  -  $this->nonGroup  ;     
          

      // Récupération des semaines du bloc 
      for($j = 0; $j < count($semaines); $j++){

        if( $nom == $semaines[$j]["nom"] ){    
          
          $check = $j;
          $index = 1; 
        
          $bloc[0]["id"] = $semaines[$j]["id"];   
          $bloc[0]["nom"] = $semaines[$j]["nom"];
          $bloc[0]["date_debut"] = $semaines[$j]["date_debut"];
          $bloc[0]["date_fin"] = $semaines[$j]["date_fin"];
          $bloc[0]["full"] = $semaines[$j]["full"];
          $bloc[0]["visible"] = $semaines[$j]["visible"];
          $bloc[0]["nom_promotion"] = $semaines[$j]["nom_promotion"];
          $bloc[0]["niveau_formation"] = $semaines[$j]["niveau_formation"];

          // boucle sur les groupes 

          $m = "m";             
          $g = "groupe";      

          for($k = 1; $k <= 5; $k++){

           
            $heure = $m . strval($k); 
            
              // $nbre correspond au nombre de groupes 
            
            for($t = 1; $t <= $nbre; $t++){
                
              $group  = $g . strval($t);   
              
              $heure = $m . strval($k); 

               // Pour le bloc                            
              $heur = 0; 
              $groupe = 0; 
              
              // Si $heure = "m1" on aura $heur = 0 etc ... 
              $heur = intval($heure[1]) - 1 ;   
              
              // Si $group = "groupe1" on aura $groupe = 0 etc ... 
              $groupe = intval($group[6]) - 1;                                          
              
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              // le 2e 0 correspond à lundi   
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["lundi"][$heure]["nom"];
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["lundi"][$heure]["type"];
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["lundi"][$heure]["profs"];
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["lundi"][$heure]["fixed"];
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "lundi";      
              $bloc[0]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;     

          
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["mardi"][$heure]["nom"];
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["mardi"][$heure]["type"];
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["mardi"][$heure]["profs"];
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["mardi"][$heure]["fixed"];
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mardi";      
              $bloc[0]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;   
  
   
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["mercredi"][$heure]["nom"];
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["mercredi"][$heure]["type"];
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["mercredi"][$heure]["profs"];
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["mercredi"][$heure]["fixed"];
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mercredi";      
              $bloc[0]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;

   
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["jeudi"][$heure]["nom"];
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["jeudi"][$heure]["type"];
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["jeudi"][$heure]["profs"];
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["jeudi"][$heure]["fixed"];
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "jeudi";      
              $bloc[0]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;


              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["vendredi"][$heure]["nom"];
              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["vendredi"][$heure]["type"];
              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["vendredi"][$heure]["profs"];
              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["vendredi"][$heure]["fixed"];
              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "vendredi";      
              $bloc[0]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;      

    

         
            }


          }     
        

        }
       

        // Pour les 3 autres semaines du bloc 
        if( ($check < $j ) && ($j <= $check + 3 )){                    


          $bloc[$index]["id"] = $semaines[$j]["id"];              
          $bloc[$index]["nom"] = $semaines[$j]["nom"];
          $bloc[$index]["date_debut"] = $semaines[$j]["date_debut"];
          $bloc[$index]["date_fin"] = $semaines[$j]["date_fin"];
          $bloc[$index]["full"] = $semaines[$j]["full"];
          $bloc[$index]["visible"] = $semaines[$j]["visible"];
          $bloc[$index]["nom_promotion"] = $semaines[$j]["nom_promotion"];  
          $bloc[$index]["niveau_formation"] = $semaines[$j]["niveau_formation"];         

          // boucle sur les groupes 

          $m = "m";
          $g = "groupe";    

          for($k = 1; $k <= 5; $k++){
     
            $heure = $m . strval($k); 
                
            for($t = 1; $t <= $nbre; $t++){  
              $group  = $g . strval($t);   
    
              $heure = $m . strval($k); 

              // Pour le bloc 
              $heur = 0; 
              $groupe = 0; 

              // Si $heure = "m1" on aura $heur = 0 etc ... 
              $heur = intval($heure[1]) - 1 ;   
              
              // Si $group = "groupe1" on aura $groupe = 0 etc ... 
              $groupe = intval($group[6]) - 1 ;                     
                         
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];
        
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["lundi"][$heure]["nom"];
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["lundi"][$heure]["type"];
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["lundi"][$heure]["profs"];
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["lundi"][$heure]["fixed"];
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "lundi";      
              $bloc[$index]["jours"][0]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;



              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["mardi"][$heure]["nom"];
              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["mardi"][$heure]["type"];
              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["mardi"][$heure]["profs"];
              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["mardi"][$heure]["fixed"];
              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mardi";      
              $bloc[$index]["jours"][1]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;   


   
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["mercredi"][$heure]["nom"];
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["mercredi"][$heure]["type"];
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["mercredi"][$heure]["profs"];
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["mercredi"][$heure]["fixed"];
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "mercredi";      
              $bloc[$index]["jours"][2]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;



              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["jeudi"][$heure]["nom"];
              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["jeudi"][$heure]["type"];
              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["jeudi"][$heure]["profs"];
              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["jeudi"][$heure]["fixed"];
              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "jeudi";      
              $bloc[$index]["jours"][3]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;

             
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["groupe_name"] = $semaines[$j][$group]["group_name"];

              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["nom"] = $semaines[$j][$group]["vendredi"][$heure]["nom"];
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["type"] = $semaines[$j][$group]["vendredi"][$heure]["type"];
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["profs"] = $semaines[$j][$group]["vendredi"][$heure]["profs"];
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["fixed"] = $semaines[$j][$group]["vendredi"][$heure]["fixed"];
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["week"] = $semaines[$j]["nom"]; 
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["day"] = "vendredi";      
              $bloc[$index]["jours"][4]["matieres"][$heur]["groupes"][$groupe]["timeline"][0]["time"] = $k;      
   
           
            }


          }

          $index++; 

        }                  
   
      } 
        

    $this->setBloc($bloc);     

    return $this->json($bloc);   
         
                        
    }





}
