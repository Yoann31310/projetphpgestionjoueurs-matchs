<?php
require_once 'Modeles/DAO/JoueurDAO.php';
require_once 'Modeles/Classes/Joueur.php';
require_once 'Modeles/connexionDB.php';
require_once 'apiGestion.php';

// On récupère l'ID si présent dans l'URL
$id = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

// On switch en fonction de la méthode
switch($methode) {
    case 'GET':
        if($id) {
            $joueur = JoueurDAO::trouver_par_id($id);
            if($joueur) {
                deliver_response(200, "Joueur trouvé", $joueur);
            } else {
                deliver_response(404, "Joueur non trouvé");
            }
        } else {
            $joueurs = JoueurDAO::recuperer_actifs();
            deliver_response(200, "Joueurs trouvés", $joueurs);
        }
        break;




    case 'POST':
        $joueur = new Joueur($data);
        if(JoueurDAO::ajouter($joueur)) {
            deliver_response(201, "Joueur ajouté");
        } else {
            deliver_response(500, "Erreur lors de l'ajout");
        }
        break;




    case 'PUT':
        $joueur = new Joueur($data);
        if(JoueurDAO::modifier($id, $joueur)) {
            deliver_response(200, "Joueur modifié");
        } else {
            deliver_response(500, "Erreur lors de la modification");
        }
        break;




    case 'DELETE':
        if(JoueurDAO::supprimer($id)) {
            deliver_response(200, "Joueur supprimé");
        } else {
            deliver_response(500, "Erreur lors de la suppression");
        }
        break;



        
    default:
        deliver_response(405, "Méthode non autorisée");
        break;
}