<?php
require_once __DIR__ . '/../connexionDB.php';
require_once __DIR__ . '/../Classes/Joueur.php';

class JoueurDAO {

    public static function recuperer_actifs() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM Joueurs WHERE statut != 'Supprimé' ORDER BY nom ASC, prenom ASC";
            $lignes = $db->query($sql)->fetchAll();
            
            $liste = [];
            foreach ($lignes as $ligne) {
                $joueur = new Joueur();
                $joueur->set_id_joueurs($ligne['Id_Joueurs']);
                $joueur->set_numero_licence($ligne['Numero_licence']);
                $joueur->set_nom($ligne['nom']);
                $joueur->set_prenom($ligne['prenom']);
                $joueur->set_date_naissance($ligne['date_naissance']);
                $joueur->set_taille($ligne['taille']);
                $joueur->set_poids($ligne['poids']);
                $joueur->set_statut($ligne['statut']);
                $liste[] = $joueur;
            }
            return $liste;
        } catch (PDOException $e) {
            error_log('[api] ' . $e->getMessage());
            return null;
        }
    }

    public static function recuperer_tout() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM Joueurs ORDER BY nom ASC, prenom ASC";
            $lignes = $db->query($sql)->fetchAll();
            
            $liste = [];
            foreach ($lignes as $ligne) {
                $joueur = new Joueur();
                $joueur->set_id_joueurs($ligne['Id_Joueurs']);
                $joueur->set_numero_licence($ligne['Numero_licence']);
                $joueur->set_nom($ligne['nom']);
                $joueur->set_prenom($ligne['prenom']);
                $joueur->set_date_naissance($ligne['date_naissance']);
                $joueur->set_taille($ligne['taille']);
                $joueur->set_poids($ligne['poids']);
                $joueur->set_statut($ligne['statut']);
                $liste[] = $joueur;
            }
            return $liste;
        } catch (PDOException $e) {
            error_log('[api] ' . $e->getMessage());
            return null;
        }
    }

    public static function trouver_par_id($id) {
        try {
            $db = Database::getInstance();
            $req = $db->prepare("SELECT * FROM Joueurs WHERE Id_Joueurs = :id");
            $req->execute(['id' => $id]);
            $ligne = $req->fetch();
            
            if ($ligne) {
                $joueur = new Joueur();
                $joueur->set_id_joueurs($ligne['Id_Joueurs']);
                $joueur->set_numero_licence($ligne['Numero_licence']);
                $joueur->set_nom($ligne['nom']);
                $joueur->set_prenom($ligne['prenom']);
                $joueur->set_date_naissance($ligne['date_naissance']);
                $joueur->set_taille($ligne['taille']);
                $joueur->set_poids($ligne['poids']);
                $joueur->set_statut($ligne['statut']);
                return $joueur;
            }
            return null;
        } catch (PDOException $e) {
            error_log('[api] ' . $e->getMessage());
            return null;
        }
    }

    public static function trouver_par_licence($licence) {
        try {
            $db = Database::getInstance();
            $req = $db->prepare("SELECT * FROM Joueurs WHERE Numero_licence = :licence");
            $req->execute(['licence' => $licence]);
            $ligne = $req->fetch();
            
            if ($ligne) {
                $joueur = new Joueur();
                $joueur->set_id_joueurs($ligne['Id_Joueurs']);
                $joueur->set_numero_licence($ligne['Numero_licence']);
                $joueur->set_nom($ligne['nom']);
                $joueur->set_prenom($ligne['prenom']);
                $joueur->set_date_naissance($ligne['date_naissance']);
                $joueur->set_taille($ligne['taille']);
                $joueur->set_poids($ligne['poids']);
                $joueur->set_statut($ligne['statut']);
                return $joueur;
            }
            return null;
        } catch (PDOException $e) {
            error_log('[api] ' . $e->getMessage());
            return null;
        }
    }

    public static function trouver_par_nom_prenom($nom, $prenom, $id_exclu = 0) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM Joueurs WHERE nom = :nom AND prenom = :prenom AND Id_Joueurs != :id AND statut != 'Supprimé' LIMIT 1";
            $req = $db->prepare($sql);
            $req->execute(['nom' => $nom, 'prenom' => $prenom, 'id' => $id_exclu]);
            $ligne = $req->fetch();
            
            if ($ligne) {
                $joueur = new Joueur();
                $joueur->set_id_joueurs($ligne['Id_Joueurs']);
                $joueur->set_numero_licence($ligne['Numero_licence']);
                $joueur->set_nom($ligne['nom']);
                $joueur->set_prenom($ligne['prenom']);
                $joueur->set_date_naissance($ligne['date_naissance']);
                $joueur->set_taille($ligne['taille']);
                $joueur->set_poids($ligne['poids']);
                $joueur->set_statut($ligne['statut']);
                return $joueur;
            }
            return null;
        } catch (PDOException $e) {
            error_log('[api] ' . $e->getMessage());
            return null;
        }
    }

    public static function licence_existe_deja($licence, $id_exclu = 0) {
        try {
            $db = Database::getInstance();
            $req = $db->prepare("SELECT COUNT(*) as nb FROM Joueurs WHERE Numero_licence = :licence AND Id_Joueurs != :id");
            $req->execute(['licence' => $licence, 'id' => $id_exclu]);
            $resultat = $req->fetch();
            return $resultat['nb'] > 0;
        } catch (PDOException $e) {
            error_log('[api] ' . $e->getMessage());
            return false;
        }
    }

    public static function ajouter(Joueur $joueur) {
        try {
            $db = Database::getInstance();
            $sql = "INSERT INTO Joueurs (nom, prenom, Numero_licence, date_naissance, taille, poids, statut) 
                    VALUES (:nom, :prenom, :licence, :naissance, :taille, :poids, :statut)";
            $req = $db->prepare($sql);
            return $req->execute([
                'nom'       => $joueur->get_nom(),
                'prenom'    => $joueur->get_prenom(),
                'licence'   => $joueur->get_numero_licence(),
                'naissance' => $joueur->get_date_naissance(),
                'taille'    => $joueur->get_taille(),
                'poids'     => $joueur->get_poids(),
                'statut'    => $joueur->get_statut()
            ]);
        } catch (PDOException $e) {
            error_log('[api] ' . $e->getMessage());
            return false;
        }
    }

    public static function modifier($id, Joueur $joueur) {
        try {
            $db = Database::getInstance();
            $sql = "UPDATE Joueurs SET nom = :nom, prenom = :prenom, Numero_licence = :licence, 
                    date_naissance = :naissance, taille = :taille, poids = :poids, statut = :statut 
                    WHERE Id_Joueurs = :id";
            $req = $db->prepare($sql);
            return $req->execute([
                'nom'       => $joueur->get_nom(),
                'prenom'    => $joueur->get_prenom(),
                'licence'   => $joueur->get_numero_licence(),
                'naissance' => $joueur->get_date_naissance(),
                'taille'    => $joueur->get_taille(),
                'poids'     => $joueur->get_poids(),
                'statut'    => $joueur->get_statut(),
                'id'        => $id
            ]);
        } catch (PDOException $e) {
            error_log('[api] ' . $e->getMessage());
            return false;
        }
    }

    public static function supprimer($id) {
        try {
            if (self::a_participe($id)) {
                return false;
            }
            $db = Database::getInstance();
            $req = $db->prepare("UPDATE Joueurs SET statut = 'Supprimé' WHERE Id_Joueurs = :id");
            return $req->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log('[api] ' . $e->getMessage());
            return false;
        }
    }

    public static function a_participe($id) {
        try {
            $db = Database::getInstance();
            $req = $db->prepare("SELECT COUNT(*) as nb FROM Participer WHERE Id_Joueurs = :id");
            $req->execute(['id' => $id]);
            $res = $req->fetch();
            return $res['nb'] > 0;
        } catch (PDOException $e) {
            error_log('[api] ' . $e->getMessage());
            return false;
        }
    }

    public static function ajouter_commentaire($id_joueur, $commentaire) {
        try {
            $db = Database::getInstance();
            $db->beginTransaction();

            $req1 = $db->prepare("INSERT INTO Evaluation_joueur (commentaire, date_commentaire) VALUES (:comm, CURDATE())");
            $req1->execute(['comm' => $commentaire]);
            $id_eval = $db->lastInsertId();

            $req2 = $db->prepare("INSERT INTO Evaluer (Id_Joueurs, Id_Evaluation_joueur) VALUES (:idJ, :idE)");
            $req2->execute(['idJ' => $id_joueur, 'idE' => $id_eval]);

            $db->commit();
            return true;
        } catch (PDOException $e) {
            error_log('[api] ' . $e->getMessage());
            if (isset($db)) {
                $db->rollBack();
            }
            return false;
        }
    }

    public static function compter_joueurs_actifs() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT COUNT(*) as total FROM Joueurs WHERE statut != 'Supprimé'";
            $resultat = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
            return $resultat['total'];
        } catch (PDOException $e) {
            error_log('[api] ' . $e->getMessage());
            return 0;
        }
    }

    public static function calculer_age_moyen() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT AVG(TIMESTAMPDIFF(YEAR, date_naissance, CURDATE())) as age_moyen
                    FROM Joueurs WHERE statut != 'Supprimé' AND date_naissance IS NOT NULL";
            $resultat = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
            
            if ($resultat['age_moyen']) {
                return round($resultat['age_moyen'], 1);
            }
            return 0;
        } catch (PDOException $e) {
            error_log('[api] ' . $e->getMessage());
            return 0;
        }
    }

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
            error_log('[api] ' . $e->getMessage());
            return [];
        }
    }
}
