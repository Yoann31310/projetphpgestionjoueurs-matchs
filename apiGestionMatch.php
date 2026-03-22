<?php
require_once 'Modeles/DAO/MatchDAO.php';
require_once 'Modeles/Classes/Matchs.php';
require_once 'Modeles/connexionDB.php';
require_once 'apiGestion.php';

// On récupère l'identifiant s'il est présent dans l'URL
$identifiant_match = null;
if (isset($_GET['id'])) {
    $identifiant_match = $_GET['id'];
}

// Aiguillage du traitement en fonction de la méthode HTTP
switch ($methode) {
    case 'GET':
        try {
            if ($identifiant_match) {
                // Récupération d'un match spécifique par son ID
                $match_trouve = Matchs::trouver_par_id($identifiant_match);
                if ($match_trouve) {
                    deliver_response(200, "Match récupéré avec succès", $match_trouve);
                } else {
                    deliver_response(404, "Match introuvable");
                }
            } 
            else {
                // On liste tous les matchs
                $tous_les_matchs = Matchs::recuperer_tout();
                deliver_response(200, "Liste des matchs récupérée", $tous_les_matchs);
            }
        } catch (Exception $erreur) {
            deliver_response(500, "Erreur API : " . $erreur->getMessage());
        }
        break;





    case 'POST':
        try {
            // Vérification des champs obligatoires
            if (!isset($data['date_heure']) || !isset($data['nom_equipe_adverse']) || !isset($data['lieu']) || !isset($data['adresse'])) {
                deliver_response(400, "Données incomplètes.");
                exit;
            }

            $nouveau_match = new Matchs();
            $nouveau_match->set_date_heure($data['date_heure']);
            $nouveau_match->set_nom_equipe_adverse($data['nom_equipe_adverse']);
            $nouveau_match->set_lieu($data['lieu']);
            $nouveau_match->set_adresse($data['adresse']);

            if (Matchs::ajouter($nouveau_match)) {
                deliver_response(201, "Match ajouté avec succès.");
            } else {
                deliver_response(500, "Erreur lors de la création du match.");
            }
        } catch (Exception $erreur) {
            deliver_response(500, "Erreur API : " . $erreur->getMessage());
        }
        exit;




    case 'PUT':
        try {
            if ($identifiant_match === null) {
                deliver_response(400, "Identifiant du match manquant.");
                exit;
            }

            if (!isset($data['date_heure']) || !isset($data['nom_equipe_adverse']) || !isset($data['lieu']) || !isset($data['adresse'])) {
                deliver_response(400, "Données incomplètes.");
                exit;
            }

            $match_a_modifier = new Matchs();
            $match_a_modifier->set_date_heure($data['date_heure']);
            $match_a_modifier->set_nom_equipe_adverse($data['nom_equipe_adverse']);
            $match_a_modifier->set_lieu($data['lieu']);
            $match_a_modifier->set_adresse($data['adresse']);
            
            if (isset($data['resultat'])) {
                $match_a_modifier->set_resultat($data['resultat'] === "" ? null : $data['resultat']);
            } else {
                $match_a_modifier->set_resultat(null);
            }

            if (Matchs::modifier($identifiant_match, $match_a_modifier)) {
                deliver_response(200, "Match mis à jour avec succès.");
            } else {
                deliver_response(500, "Erreur lors de la modification du match.");
            }
        } catch (Exception $erreur) {
            deliver_response(500, "Erreur API : " . $erreur->getMessage());
        }
        exit;




    case 'DELETE':
        try {
            if ($identifiant_match === null) {
                deliver_response(400, "Identifiant du match manquant.");
                exit;
            }

            if (Matchs::supprimer($identifiant_match)) {
                deliver_response(200, "Match supprimé avec succès.");
            } else {
                deliver_response(500, "Erreur lors de la suppression du match.");
            }
        } catch (Exception $erreur) {
            deliver_response(500, "Erreur API : " . $erreur->getMessage());
        }
        break;

    default:
        deliver_response(405, "Méthode non autorisée");
        break;
}
?>