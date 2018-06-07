<?php
// On enregistre notre autoload.
function chargerClasse($classname)
{
    require $classname.'.php';
}

spl_autoload_register('chargerClasse');

session_start(); // On appelle session_start() Aprés avoir enregistré l'autoload.

if (isset($_GET['deconnexion']))
{
    session_destroy();
    header('Location: .');
    exit();
}

$db = new PDO('mysql:host=localhost;dbname=poo', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); // On émet une alerte à chaque fois qu'une requéte a échoué.

$manager = new PersonnagesManager($db);

if (isset($_SESSION['perso'])) // Si la session perso existe, on restaure l'objet.
{
    $perso = $_SESSION['perso'];
}

if (isset($_POST['creer']) && isset($_POST['nom'])) // Si on a voulu créer un personnage.
{
    $perso = new Personnages(['nom' => $_POST['nom']]); // On crée un nouveau personnage.
    
    if (!$perso->nomValide())
    {
        $message = 'Le nom choisi est invalide.';
        unset($perso);
    }
    elseif ($manager->exists($perso->nom()))
    {
        $message = 'Le nom du personnage est déja pris.';
        unset($perso);
    }
    else
    {
        $manager->add($perso);
    }
}

elseif (isset($_POST['utiliser']) && isset($_POST['nom'])) // Si on a voulu utiliser un personnage.
{
    if ($manager->exists($_POST['nom'])) // Si celui-ci existe.
    {
        $perso = $manager->get($_POST['nom']);
    }
    else
    {
        $message = 'Ce personnage n\'existe pas !'; // S'il n'existe pas, on affichera ce message.
    }
}

elseif (isset($_GET['frapper'])) // Si on a cliqué sur un personnage pour le frapper.
{
    if (!isset($perso))
    {
        $message = 'Merci de créer un personnage ou de vous identifier.';
    }
    
    else
    {
        if (!$manager->exists((int) $_GET['frapper']))
        {
            $message = 'Le personnage que vous voulez frapper n\'existe pas !';
        }
        
        else
        {
            $persoAFrapper = $manager->get((int) $_GET['frapper']);
            
            $retour = $perso->frapper($persoAFrapper); // On stocke dans $retour les éventuelles erreurs ou messages que renvoie la méthode frapper.
            
            switch ($retour)
            {
                case Personnages::CEST_MOI :
                    $message = 'Mais... pourquoi voulez-vous vous frapper ???';
                    break;
                    
                case Personnages::PERSONNAGE_FRAPPE :
                    $message = 'Le personnage a bien été frappé !';
                    
                    $manager->update($perso);
                    $manager->update($persoAFrapper);
                    
                    break;
                    
                case Personnages::PERSONNAGE_TUE :
                    $message = 'Vous avez tué ce personnage !';
                    
                    $manager->update($perso);
                    $manager->delete($persoAFrapper);
                    
                    break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>TP : Mini jeu de combat</title>
    
    <meta charset="UTF-8">
  </head>
  <body>
  <h1>Bienvenue dans l'aréne !</h1>
    <p>Nombre de personnages crées : <?= $manager->count() ?></p>

    
<?php
if (isset($message)) // On a un message à afficher ?
{
  echo '<p>', $message, '</p>'; // Si oui, on l'affiche.
}

if (isset($perso)) // Si on utilise un personnage (nouveau ou pas).
{
?>
    <p><a href="?deconnexion=1">Déconnexion</a></p>
    
    <fieldset>
      <legend>Mes informations</legend>
      <p>
        Nom : <?= htmlspecialchars($perso->nom()) ?><br />
        Dégats: <?= $perso->degats() ?>
      </p>
    </fieldset>
    
    <fieldset>
      <legend>Qui frapper ?</legend>
      <p>
<?php
$persos = $manager->getList($perso->nom());

if (empty($persos))
{
  echo 'Personne à frapper !';
}

else
{
  foreach ($persos as $unPerso)
  {
    echo '<a href="?frapper=', $unPerso->id(), '">', htmlspecialchars($unPerso->nom()), '</a> (dégats: ', $unPerso->degats(), ')<br />';
  }
}
?>
      </p>
    </fieldset>
<?php
}
else
{
?>
    <form action="" method="post">
      <p>
        Nom : <input type="text" name="nom" maxlength="50" />
        <input type="submit" value="Créer ce personnage" name="creer" />
        <input type="submit" value="Utiliser ce personnage" name="utiliser" />
      </p>
    </form>
<?php
}
?>
  </body>
</html>
<?php
if (isset($perso)) // Si on a créé un personnage, on le stocke dans une variable session.
{
  $_SESSION['perso'] = $perso;
}