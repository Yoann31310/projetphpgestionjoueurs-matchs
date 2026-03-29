<?php
require_once __DIR__ . '/../connexionDB.php';
require_once __DIR__ . '/../Classes/Matchs.php';

class MatchDAO {

    public static function recuperer_tout() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM Matchs ORDER BY Date_heure DESC";
            $lignes = $db->query($sql)->fetchAll();
            
            $liste = [];
            foreach ($lignes as $ligne) {
                $match = new Matchs();
                $match->set_id_matchs($ligne['Id_Matchs']);
                $match->set_date_heure($ligne['Date_heure']);
                $match->set_nom_equipe_adverse($ligne['nom_equipe_adverse']);
                $match->set_lieu($ligne['lieu']);
                $match->set_adresse($ligne['adresse']);
                $match->set_resultat($ligne['resultat']);
                $liste[] = $match;
            }
            return $liste;
        } catch (PDOException $e) {
            return null;
        }
    }

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
            return null;
        }
    }

    public static function ajouter(Matchs $match) {
        try {
            $db = Database::getInstance();
            $sql = "INSERT INTO Matchs (Date_heure, nom_equipe_adverse, lieu, adresse) 
                    VALUES (:date_heure, :adversaire, :lieu, :adresse)";
            $req = $db->prepare($sql);
            return $req->execute([
                'date_heure' => $match->get_date_heure(),
                'adversaire' => $match->get_nom_equipe_adverse(),
                'lieu'       => $match->get_lieu(),
                'adresse'    => $match->get_adresse()
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function modifier($id, Matchs $match) {
        try {
            $db = Database::getInstance();
            $sql = "UPDATE Matchs SET Date_heure = :date_heure, nom_equipe_adverse = :adversaire, 
                    lieu = :lieu, adresse = :adresse, resultat = :resultat WHERE Id_Matchs = :id";
            $req = $db->prepare($sql);
            return $req->execute([
                'date_heure' => $match->get_date_heure(),
                'adversaire' => $match->get_nom_equipe_adverse(),
                'lieu'       => $match->get_lieu(),
                'adresse'    => $match->get_adresse(),
                'resultat'   => $match->get_resultat(),
                'id'         => $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function supprimer($id) {
        try {
            $db = Database::getInstance();
            $req = $db->prepare("DELETE FROM Matchs WHERE Id_Matchs = :id");
            return $req->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

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
            
            $matchs_joues = $resultat['victoires'] + $resultat['defaites'] + $resultat['nuls'];
            if ($matchs_joues > 0) {
                $resultat['pourcentage_victoires'] = round(($resultat['victoires'] / $matchs_joues) * 100, 1);
            } else {
                $resultat['pourcentage_victoires'] = 0;
            }
            
            return $resultat;
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function obtenir_serie_en_cours() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT resultat FROM Matchs WHERE resultat IS NOT NULL ORDER BY Date_heure DESC LIMIT 10";
            $matchs = $db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($matchs)) {
                return ['type' => 'Aucun', 'nombre' => 0];
            }
            
            $premier = $matchs[0];
            $compteur = 1;
            
            for ($i = 1; $i < count($matchs); $i++) {
                if ($matchs[$i] === $premier) {
                    $compteur++;
                } else {
                    break;
                }
            }
            
            return ['type' => $premier, 'nombre' => $compteur];
        } catch (PDOException $e) {
            return ['type' => 'Erreur', 'nombre' => 0];
        }
    }

    public static function obtenir_meilleure_serie() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT resultat FROM Matchs WHERE resultat IS NOT NULL ORDER BY Date_heure ASC";
            $matchs = $db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($matchs)) {
                return 0;
            }
            
            $serie_actuelle = 0;
            $meilleure = 0;
            
            foreach ($matchs as $resultat) {
                if ($resultat === 'Gagnée') {
                    $serie_actuelle++;
                    if ($serie_actuelle > $meilleure) {
                        $meilleure = $serie_actuelle;
                    }
                } else {
                    $serie_actuelle = 0;
                }
            }
            
            return $meilleure;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public static function obtenir_prochain_match() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM Matchs WHERE Date_heure >= NOW() ORDER BY Date_heure ASC LIMIT 1";
            return $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function obtenir_dernier_resultat() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM Matchs WHERE resultat IS NOT NULL ORDER BY Date_heure DESC LIMIT 1";
            return $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}
