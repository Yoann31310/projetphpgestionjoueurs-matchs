<?php
// Définition de l'URL de l'API d'authentification
define('URL_API_AUTH', 'https://alfred.alwaysdata.net/authapi.php');
require_once 'Modeles/connexionDB.php';

// envoyer une réponse JSON au client
function deliver_response($code_statut, $message_statut, $donnees = null)
{
    http_response_code($code_statut);

    // configuration des headers CORS et Type de contenu
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Content-Type: application/json; charset=utf-8");

    $reponse = [
        'status_code' => $code_statut,
        'status_message' => $message_statut,
        'data' => $donnees
    ];

    $json_response = json_encode($reponse);
    if ($json_response === false) {
        http_response_code(500);
        echo json_encode([
            'status_code' => 500,
            'status_message' => 'Erreur interne lors de l\'encodage JSON',
            'data' => null
        ]);
        exit;
    }

    echo $json_response;
}

// Fonction pour extraire le jeton depuis le header de la requête client
function recuperer_jeton() {
    $headers = '';

    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = $_SERVER['HTTP_AUTHORIZATION'];
    }
    if (isset($_SERVER['Authorization'])) {
        $headers = $_SERVER['Authorization'];
    }

    // on cherche la présence de "Bearer <le_jeton>" dans le header trouvé
    if (preg_match('/Bearer\s(\S+)/', trim($headers), $correspondances)) {
        if ($correspondances[1] != 'null') {
            return $correspondances[1];
        }
    }
    
    // Si aucun jeton valide n'a été trouvé
    return null;
}


// Fonction pour interroger l'API distante et vérifier le jeton
function verifier_jeton_via_api($jeton) {
    // Initialisation de cURL pour faire une requête HTTP
    $curl = curl_init();

    // Configuration de base : URL cible, on attend un retour (RETURNTRANSFER), méthode GET
    curl_setopt($curl, CURLOPT_URL, URL_API_AUTH);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

    // On injecte le jeton que l'on veut vérifier dans le headers
    $headers_http = array(
        'Authorization: Bearer ' . $jeton
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers_http);

    // On exécute la requête
    $reponse = curl_exec($curl);

    // Si la requête échoue
    if ($reponse === false) {
        curl_close($curl);
        return false;
    }

    // On regarde le code HTTP renvoyé par l'API
    $code_http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // Si c'est 200, ok pour les api de gestion
    if ($code_http === 200) {
        return true;
    } else {
        return false;
    }
}

// Gestion des requêtes de pré-vérification CORS
$methode = $_SERVER['REQUEST_METHOD'];
if ($methode == 'OPTIONS') {
    deliver_response(204, "CORS Autorisé");
    exit;
}

// on récupère le jeton du client
$jeton_recu = recuperer_jeton();

if (!$jeton_recu) {
    deliver_response(401, "Authentification requise. Aucun jeton fourni.");
    exit;
}

// on envoie le jeton à l'API d'authentification pour qu'elle vérifie
$est_valide = verifier_jeton_via_api($jeton_recu);

// Si l'API d'authentification est pas ok : 
if (!$est_valide) {
    deliver_response(401, "Jeton invalide, expiré, ou API d'authentification injoignable.");
    exit;
}

// Variables partagées pour toutes les APIs
$methode = $_SERVER['REQUEST_METHOD'];
$donnees_entree = file_get_contents('php://input');
$data = json_decode($donnees_entree, true);
?>
