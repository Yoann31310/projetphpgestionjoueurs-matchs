<?php
require_once 'Modeles/DAO/MatchDAO.php';
require_once 'Modeles/DAO/ParticipationDAO.php';
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
                
                // Calcul additionnel pour le pourcentage total des matchs perdus/nuls
                $matchs_joues = $globales['victoires'] + $globales['defaites'] + $globales['nuls'];
                if ($matchs_joues > 0) {
                    $globales['pourcentage_defaites'] = round(($globales['defaites'] / $matchs_joues) * 100, 1);
                    $globales['pourcentage_nuls'] = round(($globales['nuls'] / $matchs_joues) * 100, 1);
                } else {
                    $globales['pourcentage_defaites'] = 0;
                    $globales['pourcentage_nuls'] = 0;
                }

                deliver_response(200, "Statistiques récupérées", [
                    'globales' => $globales,
                    'par_joueur' => $joueurs
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
