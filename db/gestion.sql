-- Base du service de gestion : joueurs, matchs, feuilles de match (données factices)
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

INSERT INTO Joueurs (Numero_licence, nom, prenom, date_naissance, taille, poids, statut) VALUES
(1001, 'Martin', 'Lucas', '2001-03-14', 188, 84, 'Actif'),
(1002, 'Bernard', 'Hugo', '1999-07-02', 192, 90, 'Actif'),
(1003, 'Petit', 'Nathan', '2002-11-21', 181, 77, 'Actif'),
(1004, 'Robert', 'Enzo', '2000-01-30', 185, 80, 'Actif'),
(1005, 'Richard', 'Thomas', '1998-05-18', 190, 88, 'Actif'),
(1006, 'Durand', 'Maxime', '2003-09-09', 179, 75, 'Actif'),
(1007, 'Leroy', 'Louis', '2001-12-05', 183, 79, 'Actif'),
(1008, 'Moreau', 'Adam', '1997-04-25', 187, 85, 'Blessé'),
(1009, 'Simon', 'Jules', '2002-06-12', 182, 78, 'Actif'),
(1010, 'Laurent', 'Paul', '2000-10-03', 186, 82, 'Actif'),
(1011, 'Michel', 'Axel', '2004-02-27', 178, 72, 'Actif'),
(1012, 'Garcia', 'Noé', '1999-08-16', 189, 86, 'Absent'),
(1013, 'Roux', 'Mathis', '2001-05-09', 184, 81, 'Suspendu'),
(1014, 'Fournier', 'Elias', '2000-03-22', 180, 76, 'Actif'),
(1015, 'Girard', 'Samuel', '1996-11-11', 191, 89, 'Supprimé');

INSERT INTO Matchs (Date_heure, nom_equipe_adverse, lieu, adresse, resultat) VALUES
(DATE_ADD(CONCAT(CURDATE(), ' 15:00:00'), INTERVAL -35 DAY), 'HBC Muret', 'Domicile', 'Gymnase municipal, Toulouse', 'gagnée'),
(DATE_ADD(CONCAT(CURDATE(), ' 15:00:00'), INTERVAL -28 DAY), 'AS Blagnac', 'Extérieur', 'Salle des sports, Blagnac', 'perdue'),
(DATE_ADD(CONCAT(CURDATE(), ' 15:00:00'), INTERVAL -21 DAY), 'US Colomiers', 'Domicile', 'Gymnase municipal, Toulouse', 'gagnée'),
(DATE_ADD(CONCAT(CURDATE(), ' 15:00:00'), INTERVAL -14 DAY), 'Ramonville HB', 'Extérieur', 'Complexe sportif, Ramonville', 'égalité'),
(DATE_ADD(CONCAT(CURDATE(), ' 15:00:00'), INTERVAL 7 DAY), 'Tournefeuille HB', 'Extérieur', 'Complexe de Pibrac, Tournefeuille', NULL),
(DATE_ADD(CONCAT(CURDATE(), ' 15:00:00'), INTERVAL 14 DAY), 'Cugnaux HB', 'Domicile', 'Gymnase municipal, Toulouse', NULL);

INSERT INTO Participer (Id_Matchs, Id_Joueurs, feuille_match, nom_poste, est_Capitaine, evaluation, commentaire) VALUES
(1, 2, 'titulaire', 'Gardien', 1, 8, 'Bien dans ses duels.'),
(1, 3, 'titulaire', 'Pivot', 0, 6, 'Un peu juste physiquement.'),
(1, 4, 'titulaire', 'Demi-centre', 0, 9, 'Efficace aux tirs.'),
(1, 5, 'titulaire', 'Arrière gauche', 0, 7, 'Bonne présence collective.'),
(1, 6, 'titulaire', 'Arrière droit', 0, 5, 'Très bon match, solide en défense.'),
(1, 7, 'titulaire', 'Ailier gauche', 0, 8, 'Bien dans ses duels.'),
(1, 9, 'titulaire', 'Ailier droit', 0, 7, 'Un peu juste physiquement.'),
(1, 10, 'remplaçant', 'Gardien', 0, 6, 'Efficace aux tirs.'),
(1, 11, 'remplaçant', 'Pivot', 0, 8, 'Bonne présence collective.'),
(1, 14, 'remplaçant', 'Demi-centre', 0, 7, 'Très bon match, solide en défense.'),
(2, 3, 'titulaire', 'Gardien', 1, 6, 'Un peu juste physiquement.'),
(2, 4, 'titulaire', 'Pivot', 0, 9, 'Efficace aux tirs.'),
(2, 5, 'titulaire', 'Demi-centre', 0, 7, 'Bonne présence collective.'),
(2, 6, 'titulaire', 'Arrière gauche', 0, 5, 'Très bon match, solide en défense.'),
(2, 7, 'titulaire', 'Arrière droit', 0, 8, 'Bien dans ses duels.'),
(2, 9, 'titulaire', 'Ailier gauche', 0, 7, 'Un peu juste physiquement.'),
(2, 10, 'titulaire', 'Ailier droit', 0, 6, 'Efficace aux tirs.'),
(2, 11, 'remplaçant', 'Gardien', 0, 8, 'Bonne présence collective.'),
(2, 14, 'remplaçant', 'Pivot', 0, 7, 'Très bon match, solide en défense.'),
(3, 1, 'titulaire', 'Gardien', 1, 9, 'Efficace aux tirs.'),
(3, 2, 'titulaire', 'Pivot', 0, 7, 'Bonne présence collective.'),
(3, 3, 'titulaire', 'Demi-centre', 0, 5, 'Très bon match, solide en défense.'),
(3, 4, 'titulaire', 'Arrière gauche', 0, 8, 'Bien dans ses duels.'),
(3, 5, 'titulaire', 'Arrière droit', 0, 7, 'Un peu juste physiquement.'),
(3, 6, 'titulaire', 'Ailier gauche', 0, 6, 'Efficace aux tirs.'),
(3, 7, 'titulaire', 'Ailier droit', 0, 8, 'Bonne présence collective.'),
(3, 9, 'remplaçant', 'Gardien', 0, 7, 'Très bon match, solide en défense.'),
(3, 10, 'remplaçant', 'Pivot', 0, 8, 'Bien dans ses duels.'),
(3, 11, 'remplaçant', 'Demi-centre', 0, 6, 'Un peu juste physiquement.'),
(4, 2, 'titulaire', 'Gardien', 1, 7, 'Bonne présence collective.'),
(4, 3, 'titulaire', 'Pivot', 0, 5, 'Très bon match, solide en défense.'),
(4, 4, 'titulaire', 'Demi-centre', 0, 8, 'Bien dans ses duels.'),
(4, 5, 'titulaire', 'Arrière gauche', 0, 7, 'Un peu juste physiquement.'),
(4, 6, 'titulaire', 'Arrière droit', 0, 6, 'Efficace aux tirs.'),
(4, 7, 'titulaire', 'Ailier gauche', 0, 8, 'Bonne présence collective.'),
(4, 9, 'titulaire', 'Ailier droit', 0, 7, 'Très bon match, solide en défense.'),
(4, 10, 'remplaçant', 'Gardien', 0, 8, 'Bien dans ses duels.'),
(4, 11, 'remplaçant', 'Pivot', 0, 6, 'Un peu juste physiquement.'),
(4, 14, 'remplaçant', 'Demi-centre', 0, 9, 'Efficace aux tirs.'),
(5, 3, 'titulaire', 'Gardien', 1, NULL, NULL),
(5, 4, 'titulaire', 'Pivot', 0, NULL, NULL),
(5, 5, 'titulaire', 'Demi-centre', 0, NULL, NULL),
(5, 6, 'titulaire', 'Arrière gauche', 0, NULL, NULL),
(5, 7, 'titulaire', 'Arrière droit', 0, NULL, NULL),
(5, 9, 'titulaire', 'Ailier gauche', 0, NULL, NULL),
(5, 10, 'titulaire', 'Ailier droit', 0, NULL, NULL),
(5, 11, 'remplaçant', 'Gardien', 0, NULL, NULL),
(5, 14, 'remplaçant', 'Pivot', 0, NULL, NULL),
(6, 1, 'titulaire', 'Gardien', 1, NULL, NULL),
(6, 2, 'titulaire', 'Pivot', 0, NULL, NULL),
(6, 3, 'titulaire', 'Demi-centre', 0, NULL, NULL),
(6, 4, 'titulaire', 'Arrière gauche', 0, NULL, NULL),
(6, 5, 'titulaire', 'Arrière droit', 0, NULL, NULL),
(6, 6, 'titulaire', 'Ailier gauche', 0, NULL, NULL),
(6, 7, 'titulaire', 'Ailier droit', 0, NULL, NULL),
(6, 9, 'remplaçant', 'Gardien', 0, NULL, NULL),
(6, 10, 'remplaçant', 'Pivot', 0, NULL, NULL),
(6, 11, 'remplaçant', 'Demi-centre', 0, NULL, NULL);

INSERT INTO Evaluation_joueur (commentaire, date_commentaire) VALUES
('Bonne présence sur le terrain.', CURDATE()),
('Gardien fiable, réflexes rapides.', CURDATE()),
('Rapide et précis sur les ailes.', CURDATE());
INSERT INTO Evaluer (Id_Joueurs, Id_Evaluation_joueur) VALUES (1, 1), (2, 2), (5, 3);
