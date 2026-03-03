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
        // Vérification des champs qui sont obligatoires
        if (!isset($data['numero_licence']) || !isset($data['nom']) || !isset($data['prenom'])) {
            deliver_response(400, "Données incomplètes. Le nom, prénom et numéro de licence sont obligatoires.");
            exit;
        }

        // Vérification des doublons de licence
        if (Joueur::licence_existe_deja($data['numero_licence'])) {
            deliver_response(409, "Le numéro de licence " . $data['numero_licence'] . " est déjà attribué à un autre joueur.");
            exit;
        }

        // Vérification des doublons de nom/prénom
        $doublon = Joueur::trouver_par_nom_prenom($data['nom'], $data['prenom']);
        if ($doublon) {
            if ($doublon->get_numero_licence() != $data['numero_licence']) {
                deliver_response(409, "Cette personne existe déjà (Licence : " . $doublon->get_numero_licence() . ").");
                exit;
            }
        }

        // Création de l'objet Joueur et association de ses attributs
        $joueur = new Joueur();
        
        if (isset($data['numero_licence'])) {$joueur->set_numero_licence($data['numero_licence']);}

        if (isset($data['nom'])) {$joueur->set_nom($data['nom']);}

        if (isset($data['prenom'])) {$joueur->set_prenom($data['prenom']);}

        if (isset($data['date_naissance'])) {
            if ($data['date_naissance'] == "") {$joueur->set_date_naissance(null);}
            else {$joueur->set_date_naissance($data['date_naissance']);}
        }

        if (isset($data['taille'])) {
            if ($data['taille'] == "") {$joueur->set_taille(null);}
            else {$joueur->set_taille($data['taille']);}
        }

        if (isset($data['poids'])) {
            if ($data['poids'] == "") {$joueur->set_poids(null);}
            else {$joueur->set_poids($data['poids']);}
        }

        if (isset($data['statut'])) {$joueur->set_statut($data['statut']);}

        // Appel de la base de données
        if (JoueurDAO::ajouter($joueur)) {
            deliver_response(201, "Joueur ajouté avec succès.");
        } else {
            deliver_response(500, "Erreur interne lors de l'ajout.");
        }
        exit;

    case 'PUT':
        // Vérifier qu'on envoie bien un id avec la modification
        if ($id === null) {
            deliver_response(400, "ID du joueur manquant pour la modification.");
            exit;
        }

        // Vérification des champs obligatoires
        if (!isset($data['numero_licence']) || !isset($data['nom']) || !isset($data['prenom'])) {
            deliver_response(400, "Données incomplètes. Le nom, prénom et numéro de licence sont obligatoires.");
            exit;
        }

        // Vérification si la licence n'est pas prise par un autre joueur 
        if (Joueur::licence_existe_deja($data['numero_licence'], $id)) {
            deliver_response(409, "Le numéro de licence " . $data['numero_licence'] . " est déjà attribué à un autre joueur.");
            exit;
        }

        // Vérification des doublons de nom/prénom pour un autre joueur
        $doublon = Joueur::trouver_par_nom_prenom($data['nom'], $data['prenom'], $id);
        if ($doublon) {
            if ($doublon->get_numero_licence() != $data['numero_licence']) {
                deliver_response(409, "Cette personne existe déjà (Licence : " . $doublon->get_numero_licence() . ").");
                exit;
            }
        }

        // Création de l'objet Joueur pour représenter la modification
        $joueur = new Joueur();

        if (isset($data['numero_licence'])) {$joueur->set_numero_licence($data['numero_licence']);}

        if (isset($data['nom'])) {$joueur->set_nom($data['nom']);}

        if (isset($data['prenom'])) {$joueur->set_prenom($data['prenom']);}

        if (isset($data['date_naissance'])) {
            if ($data['date_naissance'] == "") {$joueur->set_date_naissance(null);}
            else {$joueur->set_date_naissance($data['date_naissance']);}
        }

        if (isset($data['taille'])) {
            if ($data['taille'] == "") {$joueur->set_taille(null);}
            else {$joueur->set_taille($data['taille']);}
        }

        if (isset($data['poids'])) {
            if ($data['poids'] == "") {$joueur->set_poids(null);}
            else {$joueur->set_poids($data['poids']);}
        }

        if (isset($data['statut'])) {$joueur->set_statut($data['statut']);}

        // Appel de la base de données
        if (JoueurDAO::modifier($id, $joueur)) {
            deliver_response(200, "Joueur modifié avec succès.");
        } else {
            deliver_response(500, "Erreur interne lors de la modification.");
        }
        exit;




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