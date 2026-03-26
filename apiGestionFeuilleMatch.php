<?php
require_once 'Modeles/DAO/ParticipationDAO.php';
require_once 'Modeles/Classes/Participation.php';
require_once 'Modeles/Classes/Matchs.php';
require_once 'Modeles/Classes/Joueur.php';
require_once 'Modeles/connexionDB.php';
require_once 'apiGestion.php';

// On récupère les identifiants si présents
if (isset($_GET['id_match'])) { $id_match = $_GET['id_match']; } else { $id_match = null; }
if (isset($_GET['id_joueur'])) { $id_joueur = $_GET['id_joueur']; } else { $id_joueur = null; }

switch ($methode) {
    case 'GET':
        if (!$id_match) {
            deliver_response(400, "ID du match manquant.");
            exit;
        }
        $participants = Participation::recuperer_participants($id_match);
        deliver_response(200, "Participants récupérés", $participants);
        break;

    case 'POST':
        // Enregistrement complet de la feuille de match
        if (!isset($data['id_match']) || !isset($data['participants'])) {
            deliver_response(400, "Données incomplètes (id_match et participants requis).");
            exit;
        }

        $id_m = $data['id_match'];
        $match = Matchs::trouver_par_id($id_m);
        if (!$match) {
            deliver_response(404, "Match introuvable.");
            exit;
        }

        // Règle : Empêcher la modification d'une feuille de match une fois le match joué
        if ($match->est_passe()) {
            deliver_response(403, "Impossible de modifier la feuille d'un match déjà passé.");
            exit;
        }

        $participants = $data['participants'];
        $nb_titulaires = 0;
        $nb_remplacants = 0;

        // Validation des participants
        foreach ($participants as $p) {
            if (!isset($p['id_joueur']) || !isset($p['role']) || !isset($p['poste'])) {
                deliver_response(400, "Données de participation incorrectes.");
                exit;
            }

            $joueur = Joueur::trouver_par_id($p['id_joueur']);
            if (!$joueur || $joueur->get_statut() !== 'Actif') {
                deliver_response(400, "Le joueur ID {$p['id_joueur']} n'est pas actif ou n'existe pas.");
                exit;
            }

            $role_lower = mb_strtolower($p['role'], 'UTF-8');
            if ($role_lower === 'titulaire') $nb_titulaires++;
            else if ($role_lower === 'remplaçant') $nb_remplacants++;
        }

        // Règle : Nombre de titulaires entre 5 et 7
        if ($nb_titulaires < 5 || $nb_titulaires > 7) {
            deliver_response(400, "Le nombre de titulaires doit être compris entre 5 et 7 (actuel: $nb_titulaires).");
            exit;
        }

        // Règle : Nombre de remplaçants maximum 7
        if ($nb_remplacants > 7) {
            deliver_response(400, "Le nombre de remplaçants ne peut pas dépasser 7 (actuel: $nb_remplacants).");
            exit;
        }

        // Tout est OK, on enregistre
        Participation::vider_feuille($id_m);
        foreach ($participants as $p) {
            Participation::ajouter_participant($id_m, $p['id_joueur'], $p['role'], $p['poste']);
        }

        deliver_response(200, "Feuille de match enregistrée avec succès.");
        break;

    case 'PUT':
        // Évaluation d'un joueur après le match
        if (!$id_match || !$id_joueur) {
            deliver_response(400, "ID match et ID joueur manquants.");
            exit;
        }

        $match = Matchs::trouver_par_id($id_match);
        if (!$match) {
            deliver_response(404, "Match introuvable.");
            exit;
        }

        // Si on a les champs d'évaluation, c'est une évaluation
        if (isset($data['evaluation']) || isset($data['commentaire'])) {
            if (isset($data['evaluation'])) {
                $note = $data['evaluation'];
            } else {
                $note = null;
            }
            
            if (isset($data['commentaire'])) {
                $comm = $data['commentaire'];
            } else {
                $comm = "";
            }
            
            if (Participation::evaluer_joueur($id_match, $id_joueur, $note, $comm)) {
                deliver_response(200, "Évaluation enregistrée.");
            } else {
                deliver_response(500, "Erreur lors de l'évaluation.");
            }
        } 
        // Sinon, c'est peut-être une modification de participation (rôle/poste) avant le match
        else if (isset($data['role']) && isset($data['poste'])) {
             if ($match->est_passe()) {
                deliver_response(403, "Impossible de modifier la participation d'un match passé.");
                exit;
            }
        } 
        break;

    case 'DELETE':
        // Retirer un joueur d'une feuille de match
        if (!$id_match || !$id_joueur) {
            deliver_response(400, "ID match et ID joueur manquants.");
            exit;
        }

        $match = Matchs::trouver_par_id($id_match);
        if (!$match) {
            deliver_response(404, "Match introuvable.");
            exit;
        }

        if ($match->est_passe()) {
            deliver_response(403, "Impossible de modifier la feuille d'un match déjà passé.");
            exit;
        }

        if (Participation::retirer_participant($id_match, $id_joueur)) {
            deliver_response(200, "Joueur retiré de la feuille de match.");
        } else {
            deliver_response(500, "Erreur lors du retrait.");
        }
        break;

    default:
        deliver_response(405, "Méthode non autorisée");
        break;
}
