<?php

class PersonnagesManager
{
    private $_db; // Instance de PDO
    
    public function __construct($db)
    {
        $this->setDb($db);
    }
    
    public function add(Personnages $perso)
    {
        $q = $this->_db->prepare('INSERT INTO personnages(nom) VALUES(:nom)');
        $q->bindValue(':nom', $perso->nom());
        $q->execute();
        
        $perso->hydrate([
            'id' => $this->_db->lastInsertId(),
            'degats' => 0,
        ]);
    }
    
    public function count()
    {
        return $this->_db->query('SELECT COUNT(*) FROM personnages')->fetchColumn();
    }
    
    public function delete(Personnages $perso)
    {
        $this->_db->exec('DELETE FROM personnages WHERE id = '.$perso->id());
    }
    
    public function exists($info)
    {
        if (is_int($info)) // On veut voir si tel personnage ayant pour id $info existe.
        {
            return (bool) $this->_db->query('SELECT COUNT(*) FROM personnages WHERE id = '.$info)->fetchColumn();
        }
        
        // Sinon, c'est qu'on veut vérifier que le nom existe ou pas.
        
        $q = $this->_db->prepare('SELECT COUNT(*) FROM personnages WHERE nom = :nom');
        $q->execute([':nom' => $info]);
        
        return (bool) $q->fetchColumn();
    }
    
    public function get($info)
    {
        if (is_int($info))
        {
            $q = $this->_db->query('SELECT id, nom, degats FROM personnages WHERE id = '.$info);
            $donnees = $q->fetch(PDO::FETCH_ASSOC);
            
            return new Personnages($donnees);
        }
        else
        {
            $q = $this->_db->prepare('SELECT id, nom, degats FROM personnages WHERE nom = :nom');
            $q->execute([':nom' => $info]);
            
            return new Personnages($q->fetch(PDO::FETCH_ASSOC));
        }
    }
    
    public function getList($nom)
    {
        $persos = [];
        
        $q = $this->_db->prepare('SELECT id, nom, degats FROM personnages WHERE nom <> :nom ORDER BY nom');
        $q->execute([':nom' => $nom]);
        
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
        {
            $persos[] = new Personnages($donnees);
        }
        
        return $persos;
    }
    
    public function update(Personnages $perso)
    {
        $q = $this->_db->prepare('UPDATE personnages SET degats = :degats WHERE id = :id');
        
        $q->bindValue(':degats', $perso->degats(), PDO::PARAM_INT);
        $q->bindValue(':id', $perso->id(), PDO::PARAM_INT);
        
        $q->execute();
    }
    
    public function setDb(PDO $db)
    {
        $this->_db = $db;
    }
   
}

