<?php
require_once __DIR__ . '/../connexionDB.php';
require_once __DIR__ . '/../Classes/Entraineur.php';

class EntraineurDAO
{
    // Un mot de passe n'est jamais enregistré en clair : on le hache (sauf s'il l'est déjà)
    private static function hacher($mdp)
    {
        return password_get_info($mdp)['algo'] ? $mdp : password_hash($mdp, PASSWORD_DEFAULT);
    }


    // Vérifier si l'identifiant existe et retourner l'entraîneur
    public static function verifier_identifiant($identifiant)
    {
        try {
            $db = Database::getInstance();
            $requete = $db->prepare("SELECT * FROM Entraineur WHERE identifiant = :id");
            $requete->execute(['id' => $identifiant]);
            $nombre = $requete->rowCount();

            // Vérifier qu'il y a exactement un résultat
            if ($nombre === 1) {
                $ligne = $requete->fetch();
                $entraineur = new Entraineur();
                $entraineur->set_id_entraineur($ligne['Id_Entraineur']);
                $entraineur->set_identifiant($ligne['identifiant']);
                $entraineur->set_mdp($ligne['mdp']);
                $entraineur->set_nom($ligne['nom']);
                $entraineur->set_prenom($ligne['prenom']);
                $entraineur->set_email($ligne['email']);
                return $entraineur;
            } elseif ($nombre > 1) {
                error_log('[auth] identifiant en double : ' . $identifiant);
                throw new RuntimeException('Données incohérentes.');
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log('[auth] ' . $e->getMessage());
            throw new RuntimeException('Erreur de base de données.');
        }
    }

    // Créer un nouveau compte entraîneur
    public static function creer_compte($donnees)
    {
        try {
            $db = Database::getInstance();
            $sql = "INSERT INTO Entraineur (identifiant, mdp, nom, prenom, email) 
					VALUES (:identifiant, :mdp, :nom, :prenom, :email)";
            $req = $db->prepare($sql);
            return $req->execute([
                'identifiant' => $donnees['identifiant'],
                'mdp' => self::hacher($donnees['mdp']),
                'nom' => $donnees['nom'],
                'prenom' => $donnees['prenom'],
                'email' => $donnees['email']
            ]);
        } catch (PDOException $e) {
            error_log('[auth] ' . $e->getMessage());
            throw new RuntimeException('Erreur de base de données.');
        }
    }

    // Modifier un compte entraîneur existant
    public static function modifier_compte($id_entraineur, $donnees)
    {
        try {
            $db = Database::getInstance();

            // Vérifier si le mot de passe est fourni
            if (!empty($donnees['mdp'])) {
                $sql = "UPDATE Entraineur SET nom = :nom, prenom = :prenom, 
						email = :email, mdp = :mdp WHERE Id_Entraineur = :id";
                $params = [
                    'nom' => $donnees['nom'],
                    'prenom' => $donnees['prenom'],
                    'email' => $donnees['email'],
                    'mdp' => self::hacher($donnees['mdp']),
                    'id' => $id_entraineur
                ];
            } else {
                // Ne pas modifier le mot de passe s'il est vide
                $sql = "UPDATE Entraineur SET nom = :nom, prenom = :prenom, 
						email = :email WHERE Id_Entraineur = :id";
                $params = [
                    'nom' => $donnees['nom'],
                    'prenom' => $donnees['prenom'],
                    'email' => $donnees['email'],
                    'id' => $id_entraineur
                ];
            }

            $req = $db->prepare($sql);
            return $req->execute($params);
        } catch (PDOException $e) {
            error_log('[auth] ' . $e->getMessage());
            throw new RuntimeException('Erreur de base de données.');
        }
    }

    // Supprimer définitivement un compte entraîneur
    public static function supprimer_compte($id_entraineur)
    {
        try {
            $db = Database::getInstance();
            $req = $db->prepare("DELETE FROM Entraineur WHERE Id_Entraineur = :id");
            return $req->execute(['id' => $id_entraineur]);
        } catch (PDOException $e) {
            error_log('[auth] ' . $e->getMessage());
            throw new RuntimeException('Erreur de base de données.');
        }
    }

    // Trouver un entraîneur par son identifiant
    public static function trouver_par_id($id)
    {
        try {
            $db = Database::getInstance();
            $req = $db->prepare("SELECT * FROM Entraineur WHERE Id_Entraineur = :id");
            $req->execute(['id' => $id]);
            $ligne = $req->fetch();

            if ($ligne) {
                $entraineur = new Entraineur();
                $entraineur->set_id_entraineur($ligne['Id_Entraineur']);
                $entraineur->set_identifiant($ligne['identifiant']);
                $entraineur->set_mdp($ligne['mdp']);
                $entraineur->set_nom($ligne['nom']);
                $entraineur->set_prenom($ligne['prenom']);
                $entraineur->set_email($ligne['email']);
                return $entraineur;
            }
            return null;
        } catch (PDOException $e) {
            error_log('[auth] ' . $e->getMessage());
            throw new RuntimeException('Erreur de base de données.');
        }
    }
}
