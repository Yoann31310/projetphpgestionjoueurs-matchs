<?php
require_once __DIR__ . '/../config.php';
demarrer_session();

require_once 'config_api.php';

if (!verifier_authentification()) {
        header('Location: ../Vues/PageConnexion.php');
        exit();
}

csrf_verifier(); // refuse les formulaires venant d'un autre site

require_once '../Modeles/Classes/Matchs.php';
require_once '../Modeles/Classes/Participation.php';

class ControleurEvaluationMatch
{

        // Afficher la page d'évaluation d'un match
        public function afficher_evaluation()
        {
                // Vérifier que l'ID du match est fourni
                if (isset($_GET['id'])) {
                        $id_match = (int) $_GET['id'];

                        // L'évaluation des joueurs se fait dans la page de détail du match
                        header('Location: ControleurMatch.php?action=details&id=' . $id_match);
                        exit();
                }
                header('Location: ControleurMatch.php');
                exit();
        }

        // Enregistrer les évaluations de tous les joueurs
        public function enregistrer_evaluations()
        {
                $id_match = (int) ($_POST['id_match'] ?? 0);
                $tous_les_ids = is_array($_POST['id_joueurs'] ?? null) ? $_POST['id_joueurs'] : [];
                $nb_erreurs = 0;

                // Parcourir tous les joueurs et enregistrer leur évaluation
                foreach ($tous_les_ids as $id_j) {
                        // Récupérer les valeurs depuis le formulaire
                        if (isset($_POST['evaluation_' . $id_j]) && $_POST['evaluation_' . $id_j] !== '') {
                                $note = (float) $_POST['evaluation_' . $id_j];
                        } else {
                                $note = null;
                        }

                        if (isset($_POST['commentaire_' . $id_j])) {
                                $commentaire = $_POST['commentaire_' . $id_j];
                        } else {
                                $commentaire = '';
                        }

                        // Enregistrer l'évaluation dans la base de données seulement si une note est fournie
                        if ($note !== null) {
                                if (!Participation::evaluer_joueur($id_match, (int) $id_j, $note, $commentaire)) {
                                        $nb_erreurs++;
                                        $derniere_erreur = message_erreur_api("Une évaluation n'a pas pu être enregistrée.");
                                }
                        }
                }

                if ($nb_erreurs > 0) {
                        $_SESSION['erreur'] = $derniere_erreur;
                } else {
                        $_SESSION['message'] = "Les évaluations ont été enregistrées.";
                }

                // Rediriger vers la liste des matchs
                header("Location: ControleurMatch.php");
                exit();
        }
}


$gestionnaire = new ControleurEvaluationMatch();
$action = "afficher";
if (isset($_GET['action'])) {
        $action = $_GET['action'];
} else if (isset($_POST['action'])) {
        $action = $_POST['action'];
}

switch ($action) {
        case "enregistrer_evaluation":
                $gestionnaire->enregistrer_evaluations();
                break;
        default:
                $gestionnaire->afficher_evaluation();
                break;
}