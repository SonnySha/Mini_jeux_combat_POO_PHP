<?php



class Personnages
{
   private $_id;
   private $_nom;
   private $_degats;
   
   const CEST_MOI = 1;
   const PERSONNAGE_TUE = 2;
   const PERSONNAGE_FRAPPE = 3;
   
   
   public function __construct(array $donnees) {
       $this->hydrate($donnees);
   }
   
   public function hydrate(array $donnees) {
       
       foreach ($donnees as $key => $values) {
           $method = 'set' . ucfirst($key);
           
           if(method_exists($this, $method)) {
               $this->$method($values);
           }
           
       }
       
   }
   
   public function frapper(Personnages $perso) {
       if($perso->_id == $this->_id) {
           return self::CEST_MOI;
       }
       
       return $perso->recevoirDegats();
   }
   
   public function recevoirDegats() {
       $this->_degats += 5;
       
       if($this->_degats >= 100) {
           return self::PERSONNAGE_TUE;
       }
       
       return self::PERSONNAGE_FRAPPE;
       
   }
   
   public function nomValide()
   {
       return !empty($this->_nom);
   }
   
   //Listes des Getters
   public function id() {
       return $this->_id;
   }
   
   public function nom()  {
       return $this->_nom;
   }
   
   public function degats() {
       return $this->_degats;
   }
   
   //Listes des Setters
   public function setId($id) {
       $id = (int) $id;
       
       if($id >= 0) {
           $this->_id = $id;
       }   
   }
   
   public function setNom($nom) {
       if(is_string($nom)) {
           $this->_nom = $nom;
       }
   }
   
   public function setDegats($degats) {
       $degats = (int) $degats;
       
       if($degats >= 0) {
           $this->_degats = $degats;
       }
   }
   
   
   
    
}

