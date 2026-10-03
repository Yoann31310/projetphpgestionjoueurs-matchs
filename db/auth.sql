-- Base du service d'authentification (données factices)
-- Import : mysql -u utilisateur -p equipe_sport_auth < db/auth.sql
-- Compte de démonstration : identifiant « coach », mot de passe « demo1234 » (à changer en production !)

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
('coach', '$2y$10$xAkuZyVqOmbFZsE3l/tkQu9XWcblJc8tSJroOXa8/lDENEE5bKgwi', 'Dupont', 'Camille', 'coach@example.com');
