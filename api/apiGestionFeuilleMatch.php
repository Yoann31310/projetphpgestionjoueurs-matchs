<?php
// API des feuilles de match (qui joue, à quel poste, et la note donnée après le match)
//   GET  ?id_match=3                        : participants d'un match
//   POST                                    : enregistrer toute la feuille d'un match à venir
//   PUT  ?id_match=3&id_joueur=5            : changer le rôle/poste (avant le match) ou noter le joueur (après)
//   DELETE ?id_match=3&id_joueur=5          : retirer un joueur de la feuille (match à venir)
require_once __DIR__ . '/Modeles/DAO/ParticipationDAO.php';
require_once __DIR__ . '/Modeles/Classes/Participation.php';
require_once __DIR__ . '/Modeles/Classes/Matchs.php';
require_once __DIR__ . '/Modeles/Classes/Joueur.php';
require_once __DIR__ . '/apiGestion.php';

$id_match = lire_id_requete('id_match');
$id_joueur = lire_id_requete('id_joueur');

switch ($methode) {
    case 'GET':
        if (!$id_match) {
            deliver_response(400, 'ID du match manquant.');
            exit;
        }
        $participants = Participation::recuperer_participants($id_match);
        if ($participants === null) {
            deliver_response(500, 'Erreur lors de la récupération des participants.');
            exit;
        }
        deliver_response(200, 'Participants récupérés', $participants);
        break;

    case 'POST':
        // Enregistrement complet de la feuille de match
        $id_m = lire_entier_positif($data['id_match'] ?? null);
        if ($id_m === null || !isset($data['participants']) || !is_array($data['participants'])) {
            deliver_response(400, 'Données incomplètes (id_match et liste participants requis).');
            exit;
        }
        $match = Matchs::trouver_par_id($id_m);
        if (!$match) {
            deliver_response(404, 'Match introuvable.');
            exit;
        }
        // Une fois le match joué, la feuille ne change plus
        if ($match->est_passe()) {
            deliver_response(403, "Impossible de modifier la feuille d'un match déjà passé.");
            exit;
        }

        $nb_titulaires = 0;
        $nb_remplacants = 0;
        $deja_vus = [];
        $a_enregistrer = [];

        foreach ($data['participants'] as $p) {
            if (!is_array($p)) {
                deliver_response(400, 'Données de participation incorrectes.');
                exit;
            }
            $id_j = lire_entier_positif($p['id_joueur'] ?? null);
            $role = lire_valeur_liste($p['role'] ?? null, ROLES_FEUILLE);
            $poste = lire_valeur_liste($p['poste'] ?? null, POSTES_FEUILLE);
            if ($id_j === null || $role === null || $poste === null) {
                deliver_response(400, 'Chaque participant doit avoir un joueur, un rôle (titulaire ou remplaçant) et un poste valide.');
                exit;
            }
            if (isset($deja_vus[$id_j])) {
                deliver_response(400, "Le joueur ID $id_j est présent deux fois sur la feuille.");
                exit;
            }
            $deja_vus[$id_j] = true;

            $joueur = Joueur::trouver_par_id($id_j);
            if (!$joueur) {
                deliver_response(400, "Le joueur ID $id_j n'existe pas.");
                exit;
            }
            // Seuls les joueurs actifs peuvent être sélectionnés
            if ($joueur->get_statut() !== 'Actif') {
                deliver_response(400, "Le joueur ID $id_j n'est pas actif (statut : {$joueur->get_statut()}).");
                exit;
            }

            if ($role === 'titulaire') {
                $nb_titulaires++;
            } else {
                $nb_remplacants++;
            }
            $a_enregistrer[] = ['id_joueur' => $id_j, 'role' => $role, 'poste' => $poste];
        }

        // Règles : 5 à 7 titulaires, 7 remplaçants au maximum
        if ($nb_titulaires < 5 || $nb_titulaires > 7) {
            deliver_response(400, "Le nombre de titulaires doit être compris entre 5 et 7 (actuel : $nb_titulaires).");
            exit;
        }
        if ($nb_remplacants > 7) {
            deliver_response(400, "Le nombre de remplaçants ne peut pas dépasser 7 (actuel : $nb_remplacants).");
            exit;
        }

        if (ParticipationDAO::remplacer_feuille($id_m, $a_enregistrer)) {
            deliver_response(200, 'Feuille de match enregistrée avec succès.');
        } else {
            deliver_response(500, "Erreur lors de l'enregistrement de la feuille (l'ancienne feuille est conservée).");
        }
        break;

    case 'PUT':
        if (!$id_match || !$id_joueur) {
            deliver_response(400, 'ID match et ID joueur manquants.');
            exit;
        }
        $match = Matchs::trouver_par_id($id_match);
        if (!$match) {
            deliver_response(404, 'Match introuvable.');
            exit;
        }
        if (!ParticipationDAO::joueur_sur_feuille($id_match, $id_joueur)) {
            deliver_response(404, "Le joueur n'est pas sur la feuille de match.");
            exit;
        }

        // Évaluation d'un joueur (après le match) : note de 0 à 10 et commentaire
        if (array_key_exists('evaluation', $data) || array_key_exists('commentaire', $data)) {
            if (!$match->est_passe()) {
                deliver_response(403, "Impossible d'évaluer un joueur avant que le match soit joué.");
                exit;
            }
            $note = null;
            if (isset($data['evaluation']) && $data['evaluation'] !== '') {
                if (!is_numeric($data['evaluation']) || $data['evaluation'] < 0 || $data['evaluation'] > 10) {
                    deliver_response(400, 'La note doit être comprise entre 0 et 10.');
                    exit;
                }
                $note = round((float) $data['evaluation'], 2);
            }
            $commentaire = '';
            if (isset($data['commentaire']) && $data['commentaire'] !== '') {
                $commentaire = lire_texte($data['commentaire'], 1, 500);
                if ($commentaire === null) {
                    deliver_response(400, 'Le commentaire ne doit pas dépasser 500 caractères.');
                    exit;
                }
            }
            if (Participation::evaluer_joueur($id_match, $id_joueur, $note, $commentaire)) {
                deliver_response(200, 'Évaluation enregistrée.');
            } else {
                deliver_response(500, "Erreur lors de l'évaluation.");
            }
            exit;
        }

        // Modification du rôle et/ou du poste (avant le match)
        if (isset($data['role']) || isset($data['poste'])) {
            if ($match->est_passe()) {
                deliver_response(403, "Impossible de modifier la participation d'un match passé.");
                exit;
            }
            $participation_actuelle = null;
            foreach (Participation::recuperer_participants($id_match) ?? [] as $p) {
                if ($p['Id_Joueurs'] == $id_joueur) {
                    $participation_actuelle = $p;
                    break;
                }
            }
            $role = isset($data['role']) ? lire_valeur_liste($data['role'], ROLES_FEUILLE) : $participation_actuelle['feuille_match'];
            $poste = isset($data['poste']) ? lire_valeur_liste($data['poste'], POSTES_FEUILLE) : $participation_actuelle['nom_poste'];
            if ($role === null || $poste === null) {
                deliver_response(400, 'Rôle (titulaire ou remplaçant) ou poste invalide.');
                exit;
            }
            if (ParticipationDAO::modifier_participant($id_match, $id_joueur, $role, $poste)) {
                deliver_response(200, 'Participation modifiée avec succès.');
            } else {
                deliver_response(500, 'Erreur lors de la modification de la participation.');
            }
            exit;
        }

        deliver_response(400, 'Données incomplètes. Fournir evaluation/commentaire ou role/poste.');
        break;

    case 'DELETE':
        if (!$id_match || !$id_joueur) {
            deliver_response(400, 'ID match et ID joueur manquants.');
            exit;
        }
        $match = Matchs::trouver_par_id($id_match);
        if (!$match) {
            deliver_response(404, 'Match introuvable.');
            exit;
        }
        if ($match->est_passe()) {
            deliver_response(403, "Impossible de modifier la feuille d'un match déjà passé.");
            exit;
        }
        if (!ParticipationDAO::joueur_sur_feuille($id_match, $id_joueur)) {
            deliver_response(404, "Le joueur n'est pas sur la feuille de match.");
            exit;
        }
        if (Participation::retirer_participant($id_match, $id_joueur)) {
            deliver_response(200, 'Joueur retiré de la feuille de match.');
        } else {
            deliver_response(500, 'Erreur lors du retrait.');
        }
        break;

    default:
        deliver_response(405, 'Méthode non autorisée');
        break;
}
