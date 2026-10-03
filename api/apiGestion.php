<?php
// Socle commun des API de gestion : réponses JSON, vérification du jeton, lecture sécurisée des données.
// Chaque API (joueurs, matchs, feuilles de match, statistiques) commence par inclure ce fichier.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Modeles/connexionDB.php';
require_once __DIR__ . '/Modeles/validation.php';

// Origines autorisées à appeler l'API depuis un navigateur (ALLOWED_ORIGIN, séparées par des virgules)
function envoyer_entetes_cors()
{
    $origine = $_SERVER['HTTP_ORIGIN'] ?? '';
    $autorisees = array_filter(array_map('trim', explode(',', env('ALLOWED_ORIGIN', ''))));
    if ($origine !== '' && in_array($origine, $autorisees, true)) {
        header("Access-Control-Allow-Origin: $origine");
        header('Vary: Origin');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }
}

// Envoie une réponse JSON au client
function deliver_response($code_statut, $message_statut, $donnees = null)
{
    http_response_code($code_statut);
    envoyer_entetes_cors();
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');

    $json = json_encode([
        'status_code' => $code_statut,
        'status_message' => $message_statut,
        'data' => $donnees
    ]);
    if ($json === false) {
        http_response_code(500);
        $json = json_encode(['status_code' => 500, 'status_message' => 'Erreur interne lors de l\'encodage JSON', 'data' => null]);
    }
    echo $json;
}

// Toute erreur imprévue : réponse générique, détail dans les journaux du serveur uniquement
set_exception_handler(function (Throwable $e) {
    error_log('[api] ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')');
    deliver_response(500, 'Erreur interne du serveur.');
    exit;
});

// Extrait le jeton de l'en-tête "Authorization: Bearer <jeton>"
function recuperer_jeton()
{
    $en_tete = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['Authorization'] ?? '');
    if ($en_tete === '' && function_exists('apache_request_headers')) {
        foreach (apache_request_headers() as $nom => $valeur) {
            if (strtolower($nom) === 'authorization') {
                $en_tete = $valeur;
            }
        }
    }
    if (preg_match('/^Bearer\s+(\S+)$/i', trim($en_tete), $correspondances) && $correspondances[1] !== 'null') {
        return $correspondances[1];
    }
    return null;
}

// Demande à l'API d'authentification si le jeton est valide (réponse HTTP 200 = valide)
function verifier_jeton_via_api($jeton)
{
    $url = env('AUTH_API_URL');
    if (!$url) {
        throw new RuntimeException('AUTH_API_URL non configurée.');
    }
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $jeton],
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $reponse = curl_exec($curl);
    $code_http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return $reponse !== false && $code_http === 200;
}

// Lit un identifiant dans l'adresse (?id=12). Renvoie null s'il est absent, répond 400 s'il est invalide.
function lire_id_requete($cle)
{
    if (!isset($_GET[$cle])) {
        return null;
    }
    $id = lire_entier_positif($_GET[$cle]);
    if ($id === null) {
        deliver_response(400, "Le paramètre « $cle » doit être un entier positif.");
        exit;
    }
    return $id;
}

// Requête de pré-vérification envoyée par le navigateur (CORS) : pas de jeton à vérifier
$methode = $_SERVER['REQUEST_METHOD'];
if ($methode === 'OPTIONS') {
    deliver_response(204, 'CORS autorisé');
    exit;
}

// Accès réservé : un jeton valide est obligatoire
$jeton_recu = recuperer_jeton();
if (!$jeton_recu) {
    deliver_response(401, 'Authentification requise. Aucun jeton fourni.');
    exit;
}
if (!verifier_jeton_via_api($jeton_recu)) {
    deliver_response(401, "Jeton invalide, expiré, ou API d'authentification injoignable.");
    exit;
}

// Données JSON envoyées par le client : toujours un tableau (vide s'il n'y a rien à lire)
$data = [];
if (in_array($methode, ['POST', 'PUT'], true)) {
    $corps = file_get_contents('php://input');
    if ($corps !== '' && $corps !== false) {
        $data = json_decode($corps, true);
        if (!is_array($data)) {
            deliver_response(400, 'Le corps de la requête doit être un objet JSON valide.');
            exit;
        }
    }
}
