<?php
// API des matchs
//   GET            : liste de tous les matchs
//   GET ?id=3      : un match
//   POST           : programmer un match (date à venir obligatoire)
//   PUT ?id=3      : modifier un match, ou saisir son résultat une fois joué
//   DELETE ?id=3   : supprimer un match à venir
require_once __DIR__ . '/Modeles/DAO/MatchDAO.php';
require_once __DIR__ . '/Modeles/Classes/Matchs.php';
require_once __DIR__ . '/apiGestion.php';

$identifiant_match = lire_id_requete('id');

// Vérifie et nettoie les champs d'un match. $actuel = match existant (sert de valeurs par défaut en modification).
// Renvoie [message d'erreur ou null, données propres].
function valider_match(array $data, $actuel = null)
{
    $propre = [];

    $date = array_key_exists('date_heure', $data) ? lire_date_heure($data['date_heure']) : ($actuel ? $actuel->get_date_heure() : null);
    if ($date === null) {
        return ['La date et l\'heure du match sont obligatoires (format AAAA-MM-JJ HH:MM).', null];
    }
    $propre['date_heure'] = $date;

    $adversaire = array_key_exists('nom_equipe_adverse', $data) ? lire_texte($data['nom_equipe_adverse'], 2, 80) : ($actuel ? $actuel->get_nom_equipe_adverse() : null);
    if ($adversaire === null) {
        return ['Le nom de l\'équipe adverse est obligatoire (2 à 80 caractères).', null];
    }
    $propre['nom_equipe_adverse'] = $adversaire;

    $lieu = array_key_exists('lieu', $data) ? lire_valeur_liste($data['lieu'], LIEUX_MATCH) : ($actuel ? $actuel->get_lieu() : null);
    if ($lieu === null) {
        return ['Le lieu doit être « Domicile » ou « Extérieur ».', null];
    }
    $propre['lieu'] = $lieu;

    if (array_key_exists('adresse', $data)) {
        $adresse = ($data['adresse'] === null || $data['adresse'] === '') ? '' : lire_texte($data['adresse'], 1, 120);
        if ($adresse === null) {
            return ['L\'adresse ne doit pas dépasser 120 caractères.', null];
        }
    } else {
        $adresse = $actuel ? (string) $actuel->get_adresse() : '';
    }
    $propre['adresse'] = $adresse;

    $propre['resultat'] = $actuel ? $actuel->get_resultat() : null;
    if (array_key_exists('resultat', $data)) {
        if ($data['resultat'] === null || $data['resultat'] === '') {
            $propre['resultat'] = null;
        } else {
            $propre['resultat'] = lire_valeur_liste($data['resultat'], RESULTATS_MATCH);
            if ($propre['resultat'] === null) {
                return ['Le résultat doit être « Gagnée », « Perdue » ou « Égalité ».', null];
            }
        }
    }
    return [null, $propre];
}

function creer_match(array $propre)
{
    $match = new Matchs();
    $match->set_date_heure($propre['date_heure']);
    $match->set_nom_equipe_adverse($propre['nom_equipe_adverse']);
    $match->set_lieu($propre['lieu']);
    $match->set_adresse($propre['adresse']);
    $match->set_resultat($propre['resultat']);
    return $match;
}

switch ($methode) {
    case 'GET':
        if ($identifiant_match) {
            $match_trouve = Matchs::trouver_par_id($identifiant_match);
            if ($match_trouve) {
                deliver_response(200, 'Match récupéré avec succès', $match_trouve);
            } else {
                deliver_response(404, 'Match introuvable');
            }
        } else {
            $tous_les_matchs = Matchs::recuperer_tout();
            if ($tous_les_matchs === null) {
                deliver_response(500, 'Erreur lors de la récupération des matchs.');
            } else {
                deliver_response(200, 'Liste des matchs récupérée', $tous_les_matchs);
            }
        }
        break;

    case 'POST':
        [$erreur, $propre] = valider_match($data);
        if ($erreur) {
            deliver_response(400, $erreur);
            exit;
        }
        if (strtotime($propre['date_heure']) < time()) {
            deliver_response(400, 'La date du match ne peut pas être dans le passé.');
            exit;
        }
        $propre['resultat'] = null; // un match qui n'a pas eu lieu n'a pas de résultat

        if (Matchs::ajouter(creer_match($propre))) {
            deliver_response(201, 'Match ajouté avec succès.');
        } else {
            deliver_response(500, 'Erreur lors de la création du match.');
        }
        exit;

    case 'PUT':
        if ($identifiant_match === null) {
            deliver_response(400, 'Identifiant du match manquant.');
            exit;
        }
        $match_actuel = Matchs::trouver_par_id($identifiant_match);
        if (!$match_actuel) {
            deliver_response(404, 'Match introuvable');
            exit;
        }

        [$erreur, $propre] = valider_match($data, $match_actuel);
        if ($erreur) {
            deliver_response(400, $erreur);
            exit;
        }

        if ($match_actuel->est_passe()) {
            // Match déjà joué : seul le résultat peut changer
            if ($propre['date_heure'] !== lire_date_heure($match_actuel->get_date_heure())
                || $propre['nom_equipe_adverse'] !== $match_actuel->get_nom_equipe_adverse()
                || $propre['lieu'] !== $match_actuel->get_lieu()) {
                deliver_response(403, "Impossible de modifier la date, le lieu ou l'adversaire d'un match déjà passé. Seul le résultat peut être saisi.");
                exit;
            }
        } else {
            // Match à venir : la date ne peut pas être reculée dans le passé, et il n'a pas encore de résultat
            if (strtotime($propre['date_heure']) < time()) {
                deliver_response(400, 'La nouvelle date du match ne peut pas être dans le passé.');
                exit;
            }
            if ($propre['resultat'] !== null) {
                deliver_response(400, "Impossible de saisir un résultat avant que le match ait eu lieu.");
                exit;
            }
        }

        if (Matchs::modifier($identifiant_match, creer_match($propre))) {
            deliver_response(200, 'Match mis à jour avec succès.');
        } else {
            deliver_response(500, 'Erreur lors de la modification du match.');
        }
        exit;

    case 'DELETE':
        if ($identifiant_match === null) {
            deliver_response(400, 'Identifiant du match manquant.');
            exit;
        }
        $match_actuel = Matchs::trouver_par_id($identifiant_match);
        if (!$match_actuel) {
            deliver_response(404, 'Match introuvable');
            exit;
        }
        if ($match_actuel->est_passe()) {
            deliver_response(403, 'Impossible de supprimer un match déjà passé.');
            exit;
        }
        if (Matchs::supprimer($identifiant_match)) {
            deliver_response(200, 'Match supprimé avec succès.');
        } else {
            deliver_response(500, 'Erreur lors de la suppression du match.');
        }
        break;

    default:
        deliver_response(405, 'Méthode non autorisée');
        break;
}
