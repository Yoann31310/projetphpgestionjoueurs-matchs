<?php
require_once __DIR__ . '/../connexionDB.php';
require_once __DIR__ . '/../Classes/Participation.php';

class ParticipationDAO {

	// Récupérer tous les participants d'un match avec les informations des joueurs
	public static function recuperer_participants($id_match) {
		try {
			$db = Database::getInstance();
			// Jointure entre Participer et Joueurs pour avoir nom/prénom
			$sql = "SELECT p.*, j.nom, j.prenom 
					FROM Participer p 
					JOIN Joueurs j ON p.Id_Joueurs = j.Id_Joueurs 
					WHERE p.Id_Matchs = :id";
			$req = $db->prepare($sql);
			$req->execute(['id' => $id_match]);
			$resultat = $req->fetchAll();
			
			$participants_enrichis = [];
			foreach ($resultat as $ligne) {
				// Créer un objet Participation
				$participation = new Participation();
				$participation->set_id_joueurs($ligne['Id_Joueurs']);
				$participation->set_id_matchs($ligne['Id_Matchs']);
				$participation->set_feuille_match($ligne['feuille_match']);
				$participation->set_evaluation($ligne['evaluation']);
				$participation->set_nom_poste($ligne['nom_poste']);
				$participation->set_est_capitaine($ligne['est_Capitaine']);
				$participation->set_commentaire($ligne['commentaire']);
				
				// Enrichir avec les données du joueur pour l'affichage
				$participants_enrichis[] = [
					'participation'  => $participation,
					'nom'            => $ligne['nom'],
					'prenom'         => $ligne['prenom'],
					'Id_Joueurs'     => $ligne['Id_Joueurs'],
					'feuille_match'  => $ligne['feuille_match'],
					'nom_poste'      => $ligne['nom_poste'],
					'evaluation'     => $ligne['evaluation'],
					'commentaire'    => $ligne['commentaire']
				];
			}
			
			return $participants_enrichis;
		} catch (PDOException $e) {
			die("Erreur lors de la récupération des participants : " . $e->getMessage());
		}
	}

	// Ajouter un participant à la feuille de match
	public static function ajouter_participant($id_match, $id_joueur, $role, $poste) {
		try {
			$db = Database::getInstance();
			$sql = "INSERT INTO Participer (Id_Matchs, Id_Joueurs, feuille_match, nom_poste, est_Capitaine) 
					VALUES (:idM, :idJ, :role, :poste, 0)";
			$req = $db->prepare($sql);
			return $req->execute([
				'idM'   => $id_match,
				'idJ'   => $id_joueur,
				'role'  => $role,
				'poste' => $poste
			]);
		} catch (PDOException $e) {
			die("Erreur lors de l'ajout du participant : " . $e->getMessage());
		}
	}

	// Vider complètement la feuille de match
	public static function vider_feuille($id_match) {
		try {
			$db = Database::getInstance();
			$req = $db->prepare("DELETE FROM Participer WHERE Id_Matchs = :id");
			return $req->execute(['id' => $id_match]);
		} catch (PDOException $e) {
			die("Erreur lors du vidage de la feuille de match : " . $e->getMessage());
		}
	}

	// Retirer un joueur spécifique d'une feuille de match
	public static function retirer_participant($id_match, $id_joueur) {
		try {
			$db = Database::getInstance();
			$req = $db->prepare("DELETE FROM Participer WHERE Id_Matchs = :idM AND Id_Joueurs = :idJ");
			return $req->execute(['idM' => $id_match, 'idJ' => $id_joueur]);
		} catch (PDOException $e) {
			die("Erreur lors du retrait du participant : " . $e->getMessage());
		}
	}

	// Enregistrer l'évaluation d'un joueur après le match
	public static function evaluer_joueur($id_match, $id_joueur, $note, $commentaire) {
		try {
			$db = Database::getInstance();
			$sql = "UPDATE Participer SET evaluation = :note, commentaire = :comm 
					WHERE Id_Matchs = :idM AND Id_Joueurs = :idJ";
			$req = $db->prepare($sql);
			return $req->execute([
				'note' => $note,
				'comm' => $commentaire,
				'idM'  => $id_match,
				'idJ'  => $id_joueur
			]);
		} catch (PDOException $e) {
			die("Erreur lors de l'évaluation du joueur : " . $e->getMessage());
		}
	}

	// Joueur avec le plus de participations
	public static function obtenir_top_participations($limite = 5) {
		try {
			$db = Database::getInstance();
			
			$sql = "SELECT j.nom, j.prenom, COUNT(p.Id_Joueurs) as nb_participations
							FROM Joueurs j
							LEFT JOIN Participer p ON j.Id_Joueurs = p.Id_Joueurs
							WHERE j.statut != 'Supprimé'
							GROUP BY j.Id_Joueurs
							ORDER BY nb_participations DESC
							LIMIT :limite";
			
			$req = $db->prepare($sql);
			$req->bindValue(':limite', $limite, PDO::PARAM_INT);
			$req->execute();
			
			return $req->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			die("Erreur top participations : " . $e->getMessage());
		}
	}

	// Statistiques complètes par joueur
public static function obtenir_stats_joueurs() {
    $db = Database::getInstance();
    $sql = "
        SELECT 
            j.Id_Joueurs,
            j.nom,
            j.prenom,
            j.statut,
            COUNT(DISTINCT p.Id_Matchs) as nb_participations,
            SUM(CASE WHEN p.feuille_match = 'titulaire' THEN 1 ELSE 0 END) as nb_titularisations,
            SUM(CASE WHEN p.feuille_match = 'remplaçant' THEN 1 ELSE 0 END) as nb_remplacements,
            ROUND(AVG(p.evaluation), 2) as moyenne_evaluations,
            COUNT(DISTINCT CASE WHEN m.resultat = 'gagnée' THEN p.Id_Matchs END) as matchs_gagnes,
            (COUNT(DISTINCT CASE WHEN m.resultat = 'gagnée' THEN p.Id_Matchs END) * 100.0 / 
             NULLIF(COUNT(DISTINCT CASE WHEN m.resultat IS NOT NULL THEN p.Id_Matchs END), 0)) as pct_victoires,
            (SELECT nom_poste 
             FROM Participer p2 
             WHERE p2.Id_Joueurs = j.Id_Joueurs 
             GROUP BY nom_poste 
             ORDER BY AVG(p2.evaluation) DESC, COUNT(*) DESC 
             LIMIT 1) as poste_prefere
        FROM Joueurs j
        LEFT JOIN Participer p ON j.Id_Joueurs = p.Id_Joueurs
        LEFT JOIN Matchs m ON p.Id_Matchs = m.Id_Matchs
        GROUP BY j.Id_Joueurs, j.nom, j.prenom, j.statut
        ORDER BY nb_participations DESC, moyenne_evaluations DESC
    ";
    return $db->query($sql)->fetchAll();
}

// Nombre de sélections consécutives pour un joueur (en remontant le temps)
public static function obtenir_selections_consecutives($id_joueur) {
    try {
        $db = Database::getInstance();
        
        // Tous les matchs terminés ordonnés par date décroissante
        $sqlMatchs = "SELECT Id_Matchs FROM Matchs WHERE resultat IS NOT NULL ORDER BY Date_heure DESC";
        $matchs = $db->query($sqlMatchs)->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($matchs)) return 0;
        
        $consecutives = 0;
        foreach ($matchs as $id_m) {
            $req = $db->prepare("SELECT COUNT(*) FROM Participer WHERE Id_Matchs = :idM AND Id_Joueurs = :idJ");
            $req->execute(['idM' => $id_m, 'idJ' => $id_joueur]);
            if ($req->fetchColumn() > 0) {
                $consecutives++;
            } else {
                break; // On s'arrête dès qu'il y a un trou
            }
        }
        return $consecutives;
    } catch (PDOException $e) {
        die("Erreur sélections consécutives : " . $e->getMessage());
    }
}
}