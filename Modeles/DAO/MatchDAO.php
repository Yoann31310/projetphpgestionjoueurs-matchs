<?php
require_once __DIR__ . '/../connexionDB.php';
require_once __DIR__ . '/../Classes/Matchs.php';

class MatchDAO {

	// Récupérer tous les matchs triés du plus récent au plus ancien
	public static function recuperer_tout() {
		try {
			$db = Database::getInstance();
			$sql = "SELECT * FROM Matchs ORDER BY Date_heure DESC";
			$resultat = $db->query($sql)->fetchAll();
			
			$matchs = [];
			foreach ($resultat as $ligne) {
				$match = new Matchs();
				$match->set_id_matchs($ligne['Id_Matchs']);
				$match->set_date_heure($ligne['Date_heure']);
				$match->set_nom_equipe_adverse($ligne['nom_equipe_adverse']);
				$match->set_lieu($ligne['lieu']);
				$match->set_adresse($ligne['adresse']);
				$match->set_resultat($ligne['resultat']);
				$matchs[] = $match;
			}
			return $matchs;
		} catch (PDOException $e) {
			die("Erreur lors de la récupération des matchs : " . $e->getMessage());
		}
	}

	// Trouver un match par son identifiant
	public static function trouver_par_id($id) {
		try {
			$db = Database::getInstance();
			$req = $db->prepare("SELECT * FROM Matchs WHERE Id_Matchs = :id");
			$req->execute(['id' => $id]);
			$ligne = $req->fetch();
			
			if ($ligne) {
				$match = new Matchs();
				$match->set_id_matchs($ligne['Id_Matchs']);
				$match->set_date_heure($ligne['Date_heure']);
				$match->set_nom_equipe_adverse($ligne['nom_equipe_adverse']);
				$match->set_lieu($ligne['lieu']);
				$match->set_adresse($ligne['adresse']);
				$match->set_resultat($ligne['resultat']);
				return $match;
			}
			return null;
		} catch (PDOException $e) {
			die("Erreur lors de la recherche du match par ID : " . $e->getMessage());
		}
	}

	// Ajouter un nouveau match
	public static function ajouter(Matchs $match) {
		try {
			$db = Database::getInstance();
			$sql = "INSERT INTO Matchs (Date_heure, nom_equipe_adverse, lieu, adresse) 
					VALUES (:date_h, :adversaire, :lieu, :adresse)";
			$req = $db->prepare($sql);
			return $req->execute([
				'date_h'     => $match->get_date_heure(),
				'adversaire' => $match->get_nom_equipe_adverse(),
				'lieu'       => $match->get_lieu(),
				'adresse'    => $match->get_adresse()
			]);
		} catch (PDOException $e) {
			die("Erreur lors de l'ajout du match : " . $e->getMessage());
		}
	}

	// Modifier les informations d'un match
	public static function modifier($id, Matchs $match) {
		try {
			$db = Database::getInstance();
			$sql = "UPDATE Matchs SET Date_heure = :date_h, nom_equipe_adverse = :adversaire, 
					lieu = :lieu, adresse = :adresse, resultat = :resultat 
					WHERE Id_Matchs = :id";
			$req = $db->prepare($sql);
			return $req->execute([
				'date_h'     => $match->get_date_heure(),
				'adversaire' => $match->get_nom_equipe_adverse(),
				'lieu'       => $match->get_lieu(),
				'adresse'    => $match->get_adresse(),
				'resultat'   => $match->get_resultat(),
				'id'         => $id
			]);
		} catch (PDOException $e) {
			die("Erreur lors de la modification du match : " . $e->getMessage());
		}
	}

	
    // Supprimer définitivement un match
    public static function supprimer($id) {
        try {
            $db = Database::getInstance();
            $sql = "DELETE FROM Matchs WHERE Id_Matchs = :id";
            $req = $db->prepare($sql);
            return $req->execute(['id' => $id]);
        } catch (PDOException $e) {
            die("Erreur lors de la suppression du match : " . $e->getMessage());
        }
    }

	// Statistiques globales des matchs
public static function obtenir_stats_globales() {
    try {
        $db = Database::getInstance();
        
        $sql = "SELECT 
            COUNT(*) as total_matchs,
            SUM(CASE WHEN resultat = 'Gagnée' THEN 1 ELSE 0 END) as victoires,
            SUM(CASE WHEN resultat = 'perdue' THEN 1 ELSE 0 END) as defaites,
            SUM(CASE WHEN resultat = 'égalité' THEN 1 ELSE 0 END) as nuls,
            SUM(CASE WHEN resultat IS NULL THEN 1 ELSE 0 END) as a_venir
        FROM Matchs";
        
        $resultat = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        
        // Calculer le pourcentage de victoires
        $matchs_joues = $resultat['victoires'] + $resultat['defaites'] + $resultat['nuls'];
        $resultat['pourcentage_victoires'] = $matchs_joues > 0 
            ? round(($resultat['victoires'] / $matchs_joues) * 100, 1) 
            : 0;
        
        return $resultat;
    } catch (PDOException $e) {
        die("Erreur stats globales : " . $e->getMessage());
    }
}

// Série en cours (victoires/défaites consécutives)
public static function obtenir_serie_en_cours() {
    try {
        $db = Database::getInstance();
        
        // Récupérer les derniers matchs terminés, triés par date décroissante
        $sql = "SELECT resultat FROM Matchs 
                WHERE resultat IS NOT NULL 
                ORDER BY Date_heure DESC 
                LIMIT 10";
        
        $matchs = $db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($matchs)) {
            return ['type' => 'Aucun', 'nombre' => 0];
        }
        
        // Le résultat le plus récent
        $premier_resultat = $matchs[0];
        $compteur = 1;
        
        // Compter combien de fois le même résultat se répète
        for ($i = 1; $i < count($matchs); $i++) {
            if ($matchs[$i] === $premier_resultat) {
                $compteur++;
            } else {
                break; // Arrêter dès qu'on trouve un résultat différent
            }
        }
        
        return [
            'type' => $premier_resultat,
            'nombre' => $compteur
        ];
    } catch (PDOException $e) {
        die("Erreur série en cours : " . $e->getMessage());
    }
}

	// Meilleure série de victoires
	public static function obtenir_meilleure_serie() {
		try {
			$db = Database::getInstance();
			
			$sql = "SELECT resultat FROM Matchs 
					WHERE resultat IS NOT NULL 
					ORDER BY Date_heure ASC";
			
			$matchs = $db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
			
			if (empty($matchs)) {
				return 0;
			}
			
			$serie_actuelle = 0;
			$meilleure_serie = 0;
			
			foreach ($matchs as $resultat) {
				if ($resultat === 'Gagnée') {
					$serie_actuelle++;
					$meilleure_serie = max($meilleure_serie, $serie_actuelle);
				} else {
					$serie_actuelle = 0;
				}
			}
			
			return $meilleure_serie;
		} catch (PDOException $e) {
			die("Erreur meilleure série : " . $e->getMessage());
		}
	}

	// Prochain match à venir
	public static function obtenir_prochain_match() {
		try {
			$db = Database::getInstance();
			
			$sql = "SELECT * FROM Matchs 
					WHERE Date_heure >= NOW() 
					ORDER BY Date_heure ASC 
					LIMIT 1";
			
			return $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			die("Erreur prochain match : " . $e->getMessage());
		}
	}

	// Dernier résultat
	public static function obtenir_dernier_resultat() {
		try {
			$db = Database::getInstance();
			
			$sql = "SELECT * FROM Matchs 
					WHERE resultat IS NOT NULL 
					ORDER BY Date_heure DESC 
					LIMIT 1";
			
			return $db->query($sql)->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			die("Erreur dernier résultat : " . $e->getMessage());
		}
	}
}