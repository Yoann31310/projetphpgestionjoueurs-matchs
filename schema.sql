-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql-alphonse.alwaysdata.net
-- Generation Time: Mar 26, 2026 at 04:28 PM
-- Server version: 10.11.15-MariaDB
-- PHP Version: 8.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alphonse_bd_api_gestion`
--

-- --------------------------------------------------------

--
-- Table structure for table `Evaluation_joueur`
--

CREATE TABLE `Evaluation_joueur` (
  `Id_Evaluation_joueur` int(11) NOT NULL,
  `commentaire` varchar(50) DEFAULT NULL,
  `date_commentaire` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Evaluation_joueur`
--

INSERT INTO `Evaluation_joueur` (`Id_Evaluation_joueur`, `commentaire`, `date_commentaire`) VALUES
(1, 'Bonne présence sur le terrain.', '2025-11-05'),
(2, 'Défense solide mais manque d’agressivité.', '2025-10-17'),
(3, 'Gardien fiable, réflexes rapides.', '2025-10-29'),
(4, 'Bonne vision du jeu.', '2025-10-29'),
(5, 'Gardien solide, quelques erreurs.', '2025-11-05'),
(6, 'Capitaine exemplaire, leadership.', '2025-10-17'),
(7, 'Gardien prometteur, à suivre.', '2025-10-29'),
(8, 'Rapide et précis sur les ailes.', '2025-10-29'),
(9, 'Solide en défense.', '2025-10-29'),
(10, 'Peut mieux exploiter les espaces.', '2025-11-05'),
(11, 'Bonne endurance, bonne lecture du jeu.', '2025-10-29'),
(12, 'Technique correcte, peut progresser.', '2025-10-17'),
(13, 'Rapide et dynamique.', '2025-10-17'),
(14, 'Attentif et réactif.', '2025-10-17'),
(15, 'Constamment impliqué, esprit d’équipe.', '2025-11-05'),
(16, 'Gardien très réactif lors du dernier match.', '2025-11-13'),
(17, 'Capitaine attentif et motivant.', '2025-10-17'),
(18, 'Ailiers très rapide, bonne finition.', '2025-10-29'),
(19, 'Peut mieux anticiper les déplacements.', '2025-11-05'),
(20, 'Bonne technique, mais attention à la défense.', '2025-10-17');

-- --------------------------------------------------------

--
-- Table structure for table `Evaluer`
--

CREATE TABLE `Evaluer` (
  `Id_Joueurs` int(11) DEFAULT NULL,
  `Id_Evaluation_joueur` int(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Evaluer`
--

INSERT INTO `Evaluer` (`Id_Joueurs`, `Id_Evaluation_joueur`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6),
(7, 7),
(8, 8),
(9, 9),
(10, 10),
(11, 11),
(12, 12),
(13, 13),
(14, 14),
(15, 15),
(3, 16),
(6, 17),
(8, 18),
(10, 19),
(12, 20);

-- --------------------------------------------------------

--
-- Table structure for table `Joueurs`
--

CREATE TABLE `Joueurs` (
  `Id_Joueurs` int(11) NOT NULL,
  `Numero_licence` int(11) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `taille` int(11) DEFAULT NULL,
  `poids` int(11) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Joueurs`
--

INSERT INTO `Joueurs` (`Id_Joueurs`, `Numero_licence`, `nom`, `prenom`, `date_naissance`, `taille`, `poids`, `statut`) VALUES
(1, 145, 'Jean', 'Gérard', '1997-08-18', 174, 72, 'Actif'),
(2, 148, 'Martin', 'Gabriel', '2001-04-17', 183, 67, 'Actif'),
(3, 178, 'Marchand', 'Arthur', '1996-02-07', 187, 69, 'Blessé'),
(4, 278, 'Guillaume', 'Raphael', '1987-01-01', 192, 83, 'Suspendu'),
(5, 312, 'Martin', 'Luc', '1990-05-14', 178, 75, 'Actif'),
(6, 451, 'Moreau', 'Sébastien', '1985-09-22', 185, 82, 'Blessé'),
(7, 509, 'Rousseau', 'Adrien', '1994-02-03', 190, 88, 'Actif'),
(8, 627, 'Lefevre', 'Thomas', '1991-11-19', 176, 70, 'Absent'),
(9, 744, 'Petit', 'Julien', '1988-07-07', 182, 79, 'Suspendu'),
(10, 815, 'Garnier', 'Mathieu', '1993-03-28', 188, 85, 'Actif'),
(11, 902, 'Faisant', 'Jean', '1992-04-16', 181, 79, 'Actif'),
(12, 933, 'Louis', 'Kevin', '1989-12-05', 187, 84, 'Actif'),
(13, 978, 'Fabre', 'Nicolas', '1995-08-21', 175, 72, 'Actif'),
(14, 1012, 'Renaud', 'Paul', '1990-02-11', 193, 91, 'Actif'),
(15, 1044, 'Chevalier', 'Hugo', '1986-10-09', 182, 78, 'Supprimé'),
(20, 901, 'Bernardette', 'Alexandre', '2006-01-12', 120, 79, 'Supprimé'),
(21, 126, 'Henri', 'Delacroix', '2002-03-16', 150, 120, 'Actif'),
(23, 1045, 'Chevalier', 'Huguette', '2000-05-10', 80, 20, 'Actif');

-- --------------------------------------------------------

--
-- Table structure for table `Matchs`
--

CREATE TABLE `Matchs` (
  `Id_Matchs` int(11) NOT NULL,
  `Date_heure` datetime DEFAULT NULL,
  `nom_equipe_adverse` varchar(50) DEFAULT NULL,
  `lieu` varchar(50) DEFAULT NULL,
  `adresse` varchar(50) DEFAULT NULL,
  `resultat` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Matchs`
--

INSERT INTO `Matchs` (`Id_Matchs`, `Date_heure`, `nom_equipe_adverse`, `lieu`, `adresse`, `resultat`) VALUES
(1, '2025-10-17 20:15:00', 'Valbron', 'Extérieur', '8 rue des Acacias', 'égalité'),
(2, '2025-10-29 19:45:00', 'Montclair', 'Domicile', '4 rue mon Moulin', 'gagnée'),
(3, '2025-11-05 18:30:00', 'Rivière', 'Extérieur', '12 avenue des Lilas', 'perdue'),
(4, '2025-11-02 18:00:00', 'Perrin', 'Domicile', '4 rue mon Moulin', 'gagnée'),
(5, '2027-01-31 18:30:00', 'Les Faucons', 'Domicile', '4 rue mon Moulin', NULL),
(9, '2026-01-22 18:37:00', 'ddaaa', 'Domicile', 'aaa', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Participer`
--

CREATE TABLE `Participer` (
  `Id_Joueurs` int(11) DEFAULT NULL,
  `Id_Matchs` int(11) DEFAULT NULL,
  `feuille_match` varchar(11) DEFAULT NULL,
  `evaluation` float DEFAULT NULL,
  `nom_poste` varchar(50) DEFAULT NULL,
  `est_Capitaine` tinyint(1) DEFAULT NULL,
  `commentaire` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Participer`
--

INSERT INTO `Participer` (`Id_Joueurs`, `Id_Matchs`, `feuille_match`, `evaluation`, `nom_poste`, `est_Capitaine`, `commentaire`) VALUES
(15, 2, 'titulaire', NULL, 'Pivot', 0, NULL),
(13, 2, 'titulaire', NULL, 'Gardien', 0, NULL),
(11, 2, 'titulaire', NULL, 'Demi-centre', 0, NULL),
(4, 2, 'remplaçant', NULL, 'Pivot', 0, NULL),
(1, 2, 'remplaçant', NULL, 'Arrière droit', 0, NULL),
(12, 2, 'titulaire', NULL, 'Arrière gauche', 0, NULL),
(3, 2, 'titulaire', NULL, 'Arrière droit', 0, NULL),
(2, 2, 'remplaçant', NULL, 'Ailier droit', 0, NULL),
(5, 2, 'titulaire', NULL, 'Ailier gauche', 0, NULL),
(6, 2, 'titulaire', NULL, 'Ailier droit', 0, NULL),
(14, 2, 'remplaçant', NULL, 'Arrière droit', 0, NULL),
(20, 4, 'remplaçant', NULL, 'Pivot', 0, NULL),
(15, 4, 'titulaire', NULL, 'Gardien', 0, NULL),
(13, 4, 'titulaire', NULL, 'Arrière droit', 0, NULL),
(1, 4, 'remplaçant', NULL, 'Arrière gauche', 0, NULL),
(12, 4, 'titulaire', NULL, 'Arrière gauche', 0, NULL),
(3, 4, 'titulaire', NULL, 'Ailier gauche', 0, NULL),
(2, 4, 'titulaire', NULL, 'Pivot', 0, NULL),
(6, 4, 'titulaire', NULL, 'Demi-centre', 0, NULL),
(14, 4, 'titulaire', NULL, 'Ailier droit', 0, NULL),
(7, 4, 'remplaçant', NULL, 'Demi-centre', 0, NULL),
(15, 1, 'titulaire', NULL, 'Ailier droit', 0, NULL),
(10, 1, 'remplaçant', NULL, 'Ailier droit', 0, NULL),
(4, 1, 'titulaire', NULL, 'Ailier gauche', 0, NULL),
(1, 1, 'remplaçant', NULL, 'Demi-centre', 0, NULL),
(8, 1, 'titulaire', NULL, 'Gardien', 0, NULL),
(3, 1, 'titulaire', NULL, 'Arrière gauche', 0, NULL),
(2, 1, 'remplaçant', NULL, 'Arrière droit', 0, NULL),
(5, 1, 'titulaire', NULL, 'Arrière droit', 0, NULL),
(6, 1, 'titulaire', NULL, 'Demi-centre', 0, NULL),
(9, 1, 'titulaire', NULL, 'Pivot', 0, NULL),
(20, 3, 'titulaire', 0.02, 'Gardien', 0, '         aaa                                                                                                                                                   '),
(11, 3, 'titulaire', 0.02, 'Arrière gauche', 0, '     ddd                                                                                                   '),
(10, 3, 'titulaire', NULL, 'Arrière droit', 0, NULL),
(4, 3, 'titulaire', NULL, 'Demi-centre', 0, NULL),
(8, 3, 'titulaire', NULL, 'Ailier gauche', 0, NULL),
(3, 3, 'titulaire', NULL, 'Pivot', 0, NULL),
(2, 3, 'remplaçant', NULL, 'Pivot', 0, NULL),
(5, 3, 'remplaçant', NULL, 'Arrière gauche', 0, NULL),
(9, 3, 'titulaire', NULL, 'Ailier droit', 0, NULL),
(7, 3, 'remplaçant', NULL, 'Gardien', 0, NULL),
(21, 9, 'titulaire', NULL, 'Gardien', 0, NULL),
(15, 9, 'titulaire', NULL, 'Gardien', 0, NULL),
(13, 9, 'titulaire', NULL, 'Gardien', 0, NULL),
(11, 9, 'titulaire', NULL, 'Gardien', 0, NULL),
(10, 9, 'titulaire', NULL, 'Gardien', 0, NULL),
(15, 5, 'titulaire', NULL, 'Gardien', 0, NULL),
(11, 5, 'titulaire', NULL, 'Pivot', 0, NULL),
(10, 5, 'titulaire', NULL, 'Demi-centre', 0, NULL),
(4, 5, 'titulaire', NULL, 'Arrière gauche', 0, NULL),
(1, 5, 'titulaire', NULL, 'Arrière droit', 0, NULL),
(3, 5, 'remplaçant', NULL, 'Ailier gauche', 0, NULL),
(2, 5, 'titulaire', NULL, 'Ailier gauche', 0, NULL),
(5, 5, 'remplaçant', NULL, 'Gardien', 0, NULL),
(6, 5, 'remplaçant', NULL, 'Arrière gauche', 0, NULL),
(14, 5, 'titulaire', NULL, 'Ailier droit', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Evaluation_joueur`
--
ALTER TABLE `Evaluation_joueur`
  ADD PRIMARY KEY (`Id_Evaluation_joueur`);

--
-- Indexes for table `Evaluer`
--
ALTER TABLE `Evaluer`
  ADD KEY `Id_Joueurs` (`Id_Joueurs`),
  ADD KEY `Id_Evaluation_joueur` (`Id_Evaluation_joueur`);

--
-- Indexes for table `Joueurs`
--
ALTER TABLE `Joueurs`
  ADD PRIMARY KEY (`Id_Joueurs`),
  ADD UNIQUE KEY `Numero_licence` (`Numero_licence`);

--
-- Indexes for table `Matchs`
--
ALTER TABLE `Matchs`
  ADD PRIMARY KEY (`Id_Matchs`);

--
-- Indexes for table `Participer`
--
ALTER TABLE `Participer`
  ADD KEY `Id_Joueurs` (`Id_Joueurs`),
  ADD KEY `Id_Matchs` (`Id_Matchs`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Evaluation_joueur`
--
ALTER TABLE `Evaluation_joueur`
  MODIFY `Id_Evaluation_joueur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `Joueurs`
--
ALTER TABLE `Joueurs`
  MODIFY `Id_Joueurs` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `Matchs`
--
ALTER TABLE `Matchs`
  MODIFY `Id_Matchs` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Evaluer`
--
ALTER TABLE `Evaluer`
  ADD CONSTRAINT `Evaluer_ibfk_1` FOREIGN KEY (`Id_Joueurs`) REFERENCES `Joueurs` (`Id_Joueurs`),
  ADD CONSTRAINT `Evaluer_ibfk_2` FOREIGN KEY (`Id_Evaluation_joueur`) REFERENCES `Evaluation_joueur` (`Id_Evaluation_joueur`);

--
-- Constraints for table `Participer`
--
ALTER TABLE `Participer`
  ADD CONSTRAINT `Participer_ibfk_1` FOREIGN KEY (`Id_Joueurs`) REFERENCES `Joueurs` (`Id_Joueurs`),
  ADD CONSTRAINT `Participer_ibfk_2` FOREIGN KEY (`Id_Matchs`) REFERENCES `Matchs` (`Id_Matchs`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
