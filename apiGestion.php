<?php
// Imports des fichiers nécessaires
require_once 'connexionDB.php';
require_once 'jwt_utils.php';

// Envoyer une réponse JSON au client
function deliver_response($code_statut, $message_statut, $donnees = null)
{
    http_response_code($code_statut);

    // Configuration des headers CORS et Type de contenu
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Content-Type: application/json; charset=utf-8");

    $reponse = [
        'status_code' => $code_statut,
        'status_message' => $message_statut,
        'data' => $donnees
    ];

    $json_response = json_encode($reponse);
    if ($json_response === false) {
        die('json encode ERROR : ' . json_last_error_msg());
    }

    echo $json_response;
}

// Gestion des requêtes de pré-vérification CORS
$methode = $_SERVER['REQUEST_METHOD'];
if ($methode == 'OPTIONS') {
    deliver_response(204, "CORS Autorisé");
    exit;
}

// --- On utilise get_bearer_token() et is_jwt_valid() pour vérifier le jeton ---
$jwt = get_bearer_token();
if (!$jwt || !is_jwt_valid($jwt, 'random')) {
    deliver_response(401, "Authentification requise pour gérer les joueurs.");
    exit;
}



// --- Parser ce que l'application veut en fonction de sa méthode ---
switch($methode) {
    case 'GET':
        /* Si y'a un ID, alors on envoit le joueur/match à l'id
           Sinon -> recuperer_tous_les_joueurs()*/
        
        
        break;
        
    case 'POST': // On souhaite créer un joueur/match
        // On lit les données du json
        $data = json_decode(file_get_contents('php://input'), true);
        // Appel au DAO pour insertion
        
        
        
        
        
        break;

    case 'PUT': // On souhaite modifier un joueur/match
        // On lit les données du json
        $data = json_decode(file_get_contents('php://input'), true);
        // Appel au DAO pour modification
        
        
        
        
        
        
        break;

    case 'DELETE': // On souhaite supprimer un joueur/match
        // On lit les données du json
        $data = json_decode(file_get_contents('php://input'), true);
        // Appel au DAO pour suppression
        
        
        
        
        break;

    default:
        deliver_response(405, "Méthode non autorisée");
        break;
}
?>