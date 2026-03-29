# Service de Gestion Sportive - API de Gestion

## Introduction
Cette API constitue le cœur métier de l'application. Elle permet de gérer les joueurs / les matchs / gérer les compositions d'équipe et de calculer les statistiques de performance de l'effectif.

## Fonctionnement
Voici les services principaux offerts par cette API :
- API Joueurs : Permet de créer, lire, modifier et supprimer (logiquement) les membres de l'équipe (nom, prénom, taille, poids, numéro de licence, statut).
- API Matchs : Gère la planification des rencontres, les résultats et les informations de lieu.
- API Feuilles de Match : Permet d'organiser les participations des joueurs, de choisir leurs rôles (titulaires ou remplaçants) et leurs postes sur le terrain.
- API Statistiques : Calcule des données de performance globales et individuelles (pourcentages de victoire, nombre de buts, notes moyennes des joueurs).

## Sécurité et Authentification
Toutes les opérations au sein de cette API requièrent un jeton JWT valide de l'entraîneur. 
- Chaque requête entrante doit contenir un header qui contient un jeton
- L'API de gestion délègue la vérification de ce jeton à l'API d'authentification (`auth_api`) via son endpoint GET. 
- Si le jeton est invalide ou expiré, l'accès aux données est refusé avec un code 401.

## Liens APIs
Les différents points d'accès en production sont :
- Authentification : `https://alfred.alwaysdata.net/authapi.php` (api d'authentification)
- Gestion des Joueurs : `https://alphonse.alwaysdata.net/apiGestionJoueur.php`
- Gestion des Matchs : `https://alphonse.alwaysdata.net/apiGestionMatch.php`
- Feuille de Match : `https://alphonse.alwaysdata.net/apiGestionFeuilleMatch.php`
- Statistiques : `https://alphonse.alwaysdata.net/apiGestionStats.php`

## Base de Données
- Serveur : `mysql-alphonse.alwaysdata.net`
- Nom de la base : `alphonse_bd_api_gestion`
- Utilisateur : `alphonse`
- Mot de passe : `azertyuiop.@`

## Liens et Dépendances
- Ce dossier constitue le backend métier du projet.
- Il est consommé par le Frontend (projetphp) pour afficher et manipuler les données sportives.
- Il dépend de l'API d'authentification (`auth_api`) pour la validation des accès.

On peut tester l'api via Swagger UI en allant sur l'adresse : https://alphonse.alwaysdata.net/docs