<?php
require_once __DIR__ . '/../config.php';
demarrer_session();
require_once 'config_api.php';

if (!verifier_authentification()) {
    header('Location: ../Vues/PageConnexion.php');
    exit();
}

require_once '../Modeles/Classes/Matchs.php';
require_once '../Modeles/Classes/Joueur.php';

// Récupérer les données nécessaires via les APIs
$prochain_match = Matchs::obtenir_prochain_match();
$dernier_resultat = Matchs::obtenir_dernier_resultat();
$nb_joueurs = Joueur::compter_joueurs_actifs();

// Afficher la vue
require_once '../Vues/PageAccueil.php';