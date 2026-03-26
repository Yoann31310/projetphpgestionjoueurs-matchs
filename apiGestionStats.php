<?php
require_once 'Modeles/DAO/MatchDAO.php';
require_once 'Modeles/DAO/ParticipationDAO.php';
require_once 'Modeles/DAO/JoueurDAO.php';
require_once 'Modeles/connexionDB.php';
require_once 'apiGestion.php';

// On regarde si un ID joueur est passé pour les stats individuelles spécifiques
if (isset($_GET['id_joueur'])) {
    $id_joueur = $_GET['id_joueur'];
} else {
    $id_joueur = null;
}

switch ($methode) {
    case 'GET':
        try {
            if ($id_joueur) {
                // Stat spécifique : sélections consécutives
                $consecutives = ParticipationDAO::obtenir_selections_consecutives($id_joueur);
                deliver_response(200, "Statistiques individuelles récupérées", [
                    'id_joueur' => $id_joueur,
                    'selections_consecutives' => $consecutives
                ]);
            } else {
                // Statistiques globales et liste par joueur
                $globales = MatchDAO::obtenir_stats_globales();
                $joueurs = ParticipationDAO::obtenir_stats_joueurs();
                
                // On ajoute le prochain match et le dernier résultat directement depuis le DAO
                $prochain = MatchDAO::obtenir_prochain_match();
                $dernier = MatchDAO::obtenir_dernier_resultat();

                // On ajoute les stats de joueurs pour le tableau de bord
                $globales['nb_joueurs_actifs'] = JoueurDAO::compter_joueurs_actifs();
                $globales['age_moyen'] = JoueurDAO::calculer_age_moyen();

                deliver_response(200, "Statistiques récupérées", [
                    'globales' => $globales,
                    'par_joueur' => $joueurs,
                    'prochain_match' => $prochain,
                    'dernier_resultat' => $dernier
                ]);
            }
        } catch (Exception $erreur) {
            deliver_response(500, "Erreur API : " . $erreur->getMessage());
        }
        break;

    default:
        deliver_response(405, "Méthode non autorisée");
        break;
}
