"""Génère db/auth.sql et db/gestion.sql (structure + données factices).

Usage : python db/generer_sql.py
Les données sont inventées : aucun vrai joueur, aucun vrai compte.
Les dates des matchs sont relatives à la date d'import, pour qu'il y ait toujours des matchs passés et à venir.
"""
import os
import subprocess

ICI = os.path.dirname(os.path.abspath(__file__))
PHP = os.environ.get("PHP_BIN", "php")

# Compte de démonstration (mot de passe : demo1234) : haché avec password_hash() de PHP
mdp_clair = "demo1234"
mdp_hache = subprocess.check_output([PHP, "-r", f"echo password_hash('{mdp_clair}', PASSWORD_DEFAULT);"]).decode().strip()

AUTH = f"""-- Base du service d'authentification (données factices)
-- Import : mysql -u utilisateur -p equipe_sport_auth < db/auth.sql
-- Compte de démonstration : identifiant « coach », mot de passe « {mdp_clair} » (à changer en production !)

DROP TABLE IF EXISTS Entraineur;
CREATE TABLE Entraineur (
  Id_Entraineur INT AUTO_INCREMENT PRIMARY KEY,
  identifiant   VARCHAR(50)  NOT NULL UNIQUE,
  mdp           VARCHAR(255) NOT NULL COMMENT 'mot de passe haché (password_hash), jamais en clair',
  nom           VARCHAR(50)  NOT NULL,
  prenom        VARCHAR(50)  NOT NULL,
  email         VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO Entraineur (identifiant, mdp, nom, prenom, email) VALUES
('coach', '{mdp_hache}', 'Dupont', 'Camille', 'coach@example.com');
"""

joueurs = [
    # licence, nom, prénom, naissance, taille, poids, statut
    (1001, "Martin", "Lucas", "2001-03-14", 188, 84, "Actif"),
    (1002, "Bernard", "Hugo", "1999-07-02", 192, 90, "Actif"),
    (1003, "Petit", "Nathan", "2002-11-21", 181, 77, "Actif"),
    (1004, "Robert", "Enzo", "2000-01-30", 185, 80, "Actif"),
    (1005, "Richard", "Thomas", "1998-05-18", 190, 88, "Actif"),
    (1006, "Durand", "Maxime", "2003-09-09", 179, 75, "Actif"),
    (1007, "Leroy", "Louis", "2001-12-05", 183, 79, "Actif"),
    (1008, "Moreau", "Adam", "1997-04-25", 187, 85, "Blessé"),
    (1009, "Simon", "Jules", "2002-06-12", 182, 78, "Actif"),
    (1010, "Laurent", "Paul", "2000-10-03", 186, 82, "Actif"),
    (1011, "Michel", "Axel", "2004-02-27", 178, 72, "Actif"),
    (1012, "Garcia", "Noé", "1999-08-16", 189, 86, "Absent"),
    (1013, "Roux", "Mathis", "2001-05-09", 184, 81, "Suspendu"),
    (1014, "Fournier", "Elias", "2000-03-22", 180, 76, "Actif"),
    (1015, "Girard", "Samuel", "1996-11-11", 191, 89, "Supprimé"),
]
# matchs : décalage en jours par rapport à l'import, adversaire, lieu, adresse, résultat (None = pas encore joué)
matchs = [
    (-35, "HBC Muret", "Domicile", "Gymnase municipal, Toulouse", "gagnée"),
    (-28, "AS Blagnac", "Extérieur", "Salle des sports, Blagnac", "perdue"),
    (-21, "US Colomiers", "Domicile", "Gymnase municipal, Toulouse", "gagnée"),
    (-14, "Ramonville HB", "Extérieur", "Complexe sportif, Ramonville", "égalité"),
    (7, "Tournefeuille HB", "Extérieur", "Complexe de Pibrac, Tournefeuille", None),
    (14, "Cugnaux HB", "Domicile", "Gymnase municipal, Toulouse", None),
]
postes = ["Gardien", "Pivot", "Demi-centre", "Arrière gauche", "Arrière droit", "Ailier gauche", "Ailier droit"]
commentaires = ["Très bon match, solide en défense.", "Bien dans ses duels.", "Un peu juste physiquement.", "Efficace aux tirs.", "Bonne présence collective."]
notes = [7, 8, 6, 9, 7, 5, 8, 7, 6, 8]


def q(valeur):
    if valeur is None:
        return "NULL"
    if isinstance(valeur, (int, float)):
        return str(valeur)
    return "'" + str(valeur).replace("'", "''") + "'"


gestion = """-- Base du service de gestion : joueurs, matchs, feuilles de match (données factices)
-- Import : mysql -u utilisateur -p equipe_sport_gestion < db/gestion.sql
-- Les dates des matchs sont calculées à partir du jour d'import : il y a toujours des matchs passés et à venir.

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS Evaluer;
DROP TABLE IF EXISTS Evaluation_joueur;
DROP TABLE IF EXISTS Participer;
DROP TABLE IF EXISTS Matchs;
DROP TABLE IF EXISTS Joueurs;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE Joueurs (
  Id_Joueurs     INT AUTO_INCREMENT PRIMARY KEY,
  Numero_licence INT NOT NULL UNIQUE,
  nom            VARCHAR(50) NOT NULL,
  prenom         VARCHAR(50) NOT NULL,
  date_naissance DATE DEFAULT NULL,
  taille         INT DEFAULT NULL COMMENT 'en cm',
  poids          INT DEFAULT NULL COMMENT 'en kg',
  statut         VARCHAR(20) NOT NULL DEFAULT 'Actif' COMMENT 'Actif, Blessé, Absent, Suspendu ou Supprimé (suppression logique)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE Matchs (
  Id_Matchs          INT AUTO_INCREMENT PRIMARY KEY,
  Date_heure         DATETIME NOT NULL,
  nom_equipe_adverse VARCHAR(80) NOT NULL,
  lieu               VARCHAR(20) NOT NULL COMMENT 'Domicile ou Extérieur',
  adresse            VARCHAR(120) DEFAULT NULL,
  resultat           VARCHAR(20) DEFAULT NULL COMMENT 'gagnée, perdue ou égalité ; vide tant que le match n''a pas eu lieu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE Participer (
  Id_Matchs     INT NOT NULL,
  Id_Joueurs    INT NOT NULL,
  feuille_match VARCHAR(20) NOT NULL COMMENT 'titulaire ou remplaçant',
  nom_poste     VARCHAR(50) NOT NULL,
  est_Capitaine TINYINT(1) NOT NULL DEFAULT 0,
  evaluation    FLOAT DEFAULT NULL COMMENT 'note de 0 à 10 donnée après le match',
  commentaire   TEXT DEFAULT NULL,
  PRIMARY KEY (Id_Matchs, Id_Joueurs),
  CONSTRAINT fk_participer_match  FOREIGN KEY (Id_Matchs)  REFERENCES Matchs (Id_Matchs)   ON DELETE CASCADE,
  CONSTRAINT fk_participer_joueur FOREIGN KEY (Id_Joueurs) REFERENCES Joueurs (Id_Joueurs)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE Evaluation_joueur (
  Id_Evaluation_joueur INT AUTO_INCREMENT PRIMARY KEY,
  commentaire          VARCHAR(50) DEFAULT NULL,
  date_commentaire     DATE DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE Evaluer (
  Id_Joueurs           INT NOT NULL,
  Id_Evaluation_joueur INT NOT NULL,
  PRIMARY KEY (Id_Joueurs, Id_Evaluation_joueur),
  CONSTRAINT fk_evaluer_joueur     FOREIGN KEY (Id_Joueurs)           REFERENCES Joueurs (Id_Joueurs),
  CONSTRAINT fk_evaluer_evaluation FOREIGN KEY (Id_Evaluation_joueur) REFERENCES Evaluation_joueur (Id_Evaluation_joueur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

"""
gestion += "INSERT INTO Joueurs (Numero_licence, nom, prenom, date_naissance, taille, poids, statut) VALUES\n"
gestion += ",\n".join("(" + ", ".join(q(x) for x in j) + ")" for j in joueurs) + ";\n\n"

gestion += "INSERT INTO Matchs (Date_heure, nom_equipe_adverse, lieu, adresse, resultat) VALUES\n"
lignes = []
for decalage, adversaire, lieu, adresse, resultat in matchs:
    lignes.append("(DATE_ADD(CONCAT(CURDATE(), ' 15:00:00'), INTERVAL %d DAY), %s, %s, %s, %s)" % (decalage, q(adversaire), q(lieu), q(adresse), q(resultat)))
gestion += ",\n".join(lignes) + ";\n\n"

# Feuilles de match : 7 titulaires + 3 remplaçants parmi les joueurs actifs (ids 1 à 14 hors blessé/absent/suspendu)
actifs = [i + 1 for i, j in enumerate(joueurs) if j[6] == "Actif"]
participations = []
for numero_match in range(1, 7):
    equipe = actifs[(numero_match % 3):(numero_match % 3) + 10]
    for rang, id_joueur in enumerate(equipe):
        role = "titulaire" if rang < 7 else "remplaçant"
        note = notes[(rang + numero_match) % len(notes)] if numero_match <= 4 else None
        comm = commentaires[(rang + numero_match) % len(commentaires)] if numero_match <= 4 else None
        participations.append((numero_match, id_joueur, role, postes[rang % 7], 1 if rang == 0 else 0, note, comm))
gestion += "INSERT INTO Participer (Id_Matchs, Id_Joueurs, feuille_match, nom_poste, est_Capitaine, evaluation, commentaire) VALUES\n"
gestion += ",\n".join("(" + ", ".join(q(x) for x in p) + ")" for p in participations) + ";\n\n"

gestion += """INSERT INTO Evaluation_joueur (commentaire, date_commentaire) VALUES
('Bonne présence sur le terrain.', CURDATE()),
('Gardien fiable, réflexes rapides.', CURDATE()),
('Rapide et précis sur les ailes.', CURDATE());
INSERT INTO Evaluer (Id_Joueurs, Id_Evaluation_joueur) VALUES (1, 1), (2, 2), (5, 3);
"""

with open(os.path.join(ICI, "auth.sql"), "w", encoding="utf-8", newline="\n") as f:
    f.write(AUTH)
with open(os.path.join(ICI, "gestion.sql"), "w", encoding="utf-8", newline="\n") as f:
    f.write(gestion)
print("db/auth.sql et db/gestion.sql générés")
