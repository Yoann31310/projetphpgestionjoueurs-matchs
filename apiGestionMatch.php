<?php
require_once 'Modeles/DAO/MatchDAO.php';
require_once 'Modeles/Classes/Matchs.php';
require_once 'Modeles/connexionDB.php';
require_once 'apiGestion.php';

// on récupère l'identifiant s'il est présent dans l'URL
$identifiant_match = null;
if (isset($_GET['id'])) {
    $identifiant_match = $_GET['id'];
}

switch ($methode) {
    case 'GET':
        if ($identifiant_match) {
            // Récupération d'un match spécifique par son ID
            $match_trouve = Matchs::trouver_par_id($identifiant_match);
            if ($match_trouve) {
                deliver_response(200, "Match récupéré avec succès", $match_trouve);
            } else {
                deliver_response(404, "Match introuvable");
            }
        } else {
            // on liste tous les matchs
            $tous_les_matchs = Matchs::recuperer_tout();
            if ($tous_les_matchs === null) {
                deliver_response(500, "Erreur lors de la récupération des matchs.");
            } else {
                deliver_response(200, "Liste des matchs récupérée", $tous_les_matchs);
            }
        }
        break;

    case 'POST':
        // Vérification des champs obligatoires (existence de la clé dans le JSON)
        if (!array_key_exists('date_heure', $data) || !array_key_exists('nom_equipe_adverse', $data) || !array_key_exists('lieu', $data) || !array_key_exists('adresse', $data)) {
            deliver_response(400, "Données incomplètes. Toutes les clés (date_heure, nom_equipe_adverse, lieu, adresse) doivent être présentes.");
            exit;
        }

        // On vérifie que les champs critiques ne sont pas vides
        if (empty(trim($data['date_heure'])) || empty(trim($data['nom_equipe_adverse']))) {
            deliver_response(400, "La date_heure et le nom de l'équipe adverse ne peuvent pas être vides.");
            exit;
        }

        // on vérifie que la date du match n'est pas dans le passé
        if (strtotime($data['date_heure']) < time()) {
            deliver_response(400, "La date du match ne peut pas être dans le passé.");
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
        exit;

    case 'PUT':
        if ($identifiant_match === null) {
            deliver_response(400, "Identifiant du match manquant.");
            exit;
        }

        $match_actuel = Matchs::trouver_par_id($identifiant_match);
        if (!$match_actuel) {
            deliver_response(404, "Match introuvable");
            exit;
        }

        // si le match est déjà passé, on n'autorise que la saisie du résultat
        if ($match_actuel->est_passe()) {
            // On regarde si on essaye de modifier autre chose que le résultat
            if (isset($data['date_heure']) || isset($data['nom_equipe_adverse']) || isset($data['lieu']) || isset($data['adresse'])) {
                // Si les données envoyées sont identiques aux actuelles, pas la peine de renvoyer d'erreur
                if (isset($data['date_heure'])) {
                    $date_h = $data['date_heure'];
                } else {
                    $date_h = $match_actuel->get_date_heure();
                }

                if (isset($data['nom_equipe_adverse'])) {
                    $nom_eq = $data['nom_equipe_adverse'];
                } else {
                    $nom_eq = $match_actuel->get_nom_equipe_adverse();
                }

                if (isset($data['lieu'])) {
                    $lieu_m = $data['lieu'];
                } else {
                    $lieu_m = $match_actuel->get_lieu();
                }

                if ($date_h != $match_actuel->get_date_heure() ||
                    $nom_eq != $match_actuel->get_nom_equipe_adverse() ||
                    $lieu_m != $match_actuel->get_lieu()) {
                    
                    deliver_response(403, "Impossible de modifier les informations (date, lieu, adverse) d'un match déjà passé. Seul le résultat peut être saisi.");
                    exit;
                }
            }
        }

        // Si le match est à venir et qu'on change la date
        if (!$match_actuel->est_passe() && isset($data['date_heure'])) {
            if (strtotime($data['date_heure']) < time()) {
                deliver_response(400, "La nouvelle date du match ne peut pas être dans le passé.");
                exit;
            }
        }

        $match_a_modifier = new Matchs();
        
        if (isset($data['date_heure'])) {
            $match_a_modifier->set_date_heure($data['date_heure']);
        } else {
            $match_a_modifier->set_date_heure($match_actuel->get_date_heure());
        }

        if (isset($data['nom_equipe_adverse'])) {
            $match_a_modifier->set_nom_equipe_adverse($data['nom_equipe_adverse']);
        } else {
            $match_a_modifier->set_nom_equipe_adverse($match_actuel->get_nom_equipe_adverse());
        }

        if (isset($data['lieu'])) {
            $match_a_modifier->set_lieu($data['lieu']);
        } else {
            $match_a_modifier->set_lieu($match_actuel->get_lieu());
        }

        if (isset($data['adresse'])) {
            $match_a_modifier->set_adresse($data['adresse']);
        } else {
            $match_a_modifier->set_adresse($match_actuel->get_adresse());
        }
        
        if (isset($data['resultat'])) {
            if ($data['resultat'] === "") {
                $match_a_modifier->set_resultat(null);
            } else {
                $match_a_modifier->set_resultat($data['resultat']);
            }
        } else {
            $match_a_modifier->set_resultat($match_actuel->get_resultat());
        }

        if (Matchs::modifier($identifiant_match, $match_a_modifier)) {
            deliver_response(200, "Match mis à jour avec succès.");
        } else {
            deliver_response(500, "Erreur lors de la modification du match.");
        }
        exit;

    case 'DELETE':
        if ($identifiant_match === null) {
            deliver_response(400, "Identifiant du match manquant.");
            exit;
        }

        $match_actuel = Matchs::trouver_par_id($identifiant_match);
        if (!$match_actuel) {
            deliver_response(404, "Match introuvable");
            exit;
        }

        // On peut pas supprimer les matchs qui ont déjà eu lieu
        if ($match_actuel->est_passe()) {
            deliver_response(403, "Impossible de supprimer un match déjà passé.");
            exit;
        }

        if (Matchs::supprimer($identifiant_match)) {
            deliver_response(200, "Match supprimé avec succès.");
        } else {
            deliver_response(500, "Erreur lors de la suppression du match.");
        }
        break;

    default:
        deliver_response(405, "Méthode non autorisée");
        break;
}
