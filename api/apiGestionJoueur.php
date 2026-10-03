<?php
// API des joueurs
//   GET            : liste des joueurs actifs
//   GET ?id=12     : un joueur
//   POST           : créer un joueur
//   POST ?id=12    : ajouter un commentaire sur un joueur
//   PUT ?id=12     : modifier un joueur
//   DELETE ?id=12  : supprimer un joueur (suppression « logique » : il reste dans l'historique)
require_once __DIR__ . '/Modeles/DAO/JoueurDAO.php';
require_once __DIR__ . '/Modeles/Classes/Joueur.php';
require_once __DIR__ . '/apiGestion.php';

$id = lire_id_requete('id');

// Construit un objet Joueur à partir de données déjà vérifiées
function creer_joueur(array $propre)
{
    $joueur = new Joueur();
    $joueur->set_numero_licence($propre['numero_licence']);
    $joueur->set_nom($propre['nom']);
    $joueur->set_prenom($propre['prenom']);
    $joueur->set_date_naissance($propre['date_naissance']);
    $joueur->set_taille($propre['taille']);
    $joueur->set_poids($propre['poids']);
    $joueur->set_statut($propre['statut']);
    return $joueur;
}

// Règles de gestion communes à la création et à la modification
// ($id_exclu = joueur en cours de modification, pour ne pas le comparer à lui-même)
function verifier_doublons(array $propre, $id_exclu = 0)
{
    if (Joueur::licence_existe_deja($propre['numero_licence'], $id_exclu)) {
        deliver_response(409, "Le numéro de licence {$propre['numero_licence']} est déjà attribué à un autre joueur.");
        exit;
    }
    $doublon = Joueur::trouver_par_nom_prenom($propre['nom'], $propre['prenom'], $id_exclu);
    if ($doublon && $doublon->get_numero_licence() != $propre['numero_licence']) {
        deliver_response(409, "Cette personne existe déjà (licence : " . $doublon->get_numero_licence() . ").");
        exit;
    }
}

switch ($methode) {
    case 'GET':
        if ($id) {
            $joueur = JoueurDAO::trouver_par_id($id);
            if ($joueur) {
                deliver_response(200, 'Joueur trouvé', $joueur);
            } else {
                deliver_response(404, 'Joueur non trouvé');
            }
        } else {
            $joueurs = JoueurDAO::recuperer_actifs();
            if ($joueurs === null) {
                deliver_response(500, 'Erreur lors de la récupération des joueurs.');
            } else {
                deliver_response(200, 'Joueurs trouvés', $joueurs);
            }
        }
        break;

    case 'POST':
        // Avec un id : ajout d'un commentaire sur ce joueur
        if ($id) {
            $commentaire = lire_texte($data['commentaire'] ?? null, 1, 50);
            if ($commentaire === null) {
                deliver_response(400, 'Le commentaire est obligatoire (50 caractères maximum).');
                exit;
            }
            if (!JoueurDAO::trouver_par_id($id)) {
                deliver_response(404, 'Joueur non trouvé');
                exit;
            }
            if (Joueur::ajouter_commentaire($id, $commentaire)) {
                deliver_response(201, 'Commentaire ajouté avec succès.');
            } else {
                deliver_response(500, "Erreur lors de l'ajout du commentaire.");
            }
            exit;
        }

        // Sans id : création d'un joueur
        [$erreur, $propre] = valider_joueur($data);
        if ($erreur) {
            deliver_response(400, $erreur);
            exit;
        }
        verifier_doublons($propre);

        if (JoueurDAO::ajouter(creer_joueur($propre))) {
            deliver_response(201, 'Joueur ajouté avec succès.');
        } else {
            deliver_response(500, "Erreur interne lors de l'ajout.");
        }
        exit;

    case 'PUT':
        if ($id === null) {
            deliver_response(400, 'ID du joueur manquant pour la modification.');
            exit;
        }
        if (!JoueurDAO::trouver_par_id($id)) {
            deliver_response(404, 'Joueur non trouvé');
            exit;
        }

        // Mêmes vérifications que pour la création
        [$erreur, $propre] = valider_joueur($data);
        if ($erreur) {
            deliver_response(400, $erreur);
            exit;
        }
        verifier_doublons($propre, $id);

        if (JoueurDAO::modifier($id, creer_joueur($propre))) {
            deliver_response(200, 'Joueur modifié avec succès.');
        } else {
            deliver_response(500, 'Erreur interne lors de la modification.');
        }
        exit;

    case 'DELETE':
        if ($id === null) {
            deliver_response(400, 'ID du joueur manquant pour la suppression.');
            exit;
        }
        if (!JoueurDAO::trouver_par_id($id)) {
            deliver_response(404, 'Joueur non trouvé');
            exit;
        }

        // Un joueur ne peut pas être supprimé s'il a déjà participé à un match
        if (Joueur::a_participe($id)) {
            deliver_response(403, 'Impossible de supprimer un joueur qui a déjà participé à au moins un match.');
            exit;
        }

        if (Joueur::supprimer($id)) {
            deliver_response(200, 'Joueur supprimé (suppression logique).');
        } else {
            deliver_response(500, 'Erreur lors de la suppression.');
        }
        break;

    default:
        deliver_response(405, 'Méthode non autorisée');
        break;
}
