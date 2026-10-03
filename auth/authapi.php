<?php
// API d'authentification
//   POST : connexion (identifiant + mot de passe) -> renvoie un jeton JWT
//   GET  : vérification d'un jeton envoyé dans l'en-tête "Authorization: Bearer <jeton>"
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/jwt_utils.php';
require_once __DIR__ . '/Classes/Entraineur.php';
require_once __DIR__ . '/DAO/EntraineurDAO.php';

// Origines autorisées à appeler l'API depuis un navigateur (liste séparée par des virgules).
// Si ALLOWED_ORIGIN n'est pas défini, aucun site tiers n'est autorisé.
function envoyer_entetes_cors()
{
    $origine = $_SERVER['HTTP_ORIGIN'] ?? '';
    $autorisees = array_filter(array_map('trim', explode(',', env('ALLOWED_ORIGIN', ''))));
    if ($origine !== '' && in_array($origine, $autorisees, true)) {
        header("Access-Control-Allow-Origin: $origine");
        header('Vary: Origin');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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
        $json = json_encode(['status_code' => 500, 'status_message' => 'Erreur interne.', 'data' => null]);
    }
    echo $json;
}

// Secret qui signe les jetons : obligatoire, et assez long pour être solide
function lire_secret_jwt()
{
    $secret = env('JWT_SECRET', '');
    if (strlen($secret) < 32) {
        throw new RuntimeException('JWT_SECRET absent ou trop court (32 caractères minimum).');
    }
    return $secret;
}

try {
    $methode = $_SERVER['REQUEST_METHOD'];

    // Requête de pré-vérification envoyée par le navigateur (CORS)
    if ($methode === 'OPTIONS') {
        deliver_response(204, 'CORS autorisé');
        exit;
    }

    $secret = lire_secret_jwt();

    // GET : le jeton est-il valide ?
    if ($methode === 'GET') {
        $jeton = get_bearer_token();
        $contenu = $jeton ? verifier_jwt($jeton, $secret) : null;
        if ($contenu !== null) {
            deliver_response(200, 'Jeton valide');
        } else {
            deliver_response(401, 'Jeton invalide ou expiré');
        }
        exit;
    }

    if ($methode !== 'POST') {
        header('Allow: GET, POST, OPTIONS');
        deliver_response(405, 'Méthode non autorisée. Utilisez POST pour vous connecter ou GET pour vérifier un jeton.');
        exit;
    }

    // POST : lecture des données JSON reçues
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data) || !isset($data['identifiant'], $data['password'])
        || !is_string($data['identifiant']) || !is_string($data['password'])) {
        deliver_response(400, 'Identifiant ou mot de passe manquant.');
        exit;
    }
    $identifiant = trim($data['identifiant']);
    $mot_de_passe = $data['password'];
    if ($identifiant === '' || strlen($identifiant) > 50 || $mot_de_passe === '' || strlen($mot_de_passe) > 200) {
        deliver_response(400, 'Identifiant ou mot de passe invalide.');
        exit;
    }

    $entraineur = Entraineur::verifier_identifiant($identifiant);
    if ($entraineur && $entraineur->verifier_mot_de_passe($mot_de_passe)) {
        $duree = max(60, (int) env('JWT_TTL', '1800')); // durée de vie du jeton en secondes
        $maintenant = time();
        $jeton = generate_jwt([
            'id_entraineur' => (int) $entraineur->get_id_entraineur(),
            'identifiant' => $entraineur->get_identifiant(),
            'nom' => $entraineur->get_nom(),
            'prenom' => $entraineur->get_prenom(),
            'iat' => $maintenant,
            'exp' => $maintenant + $duree
        ], $secret);
        deliver_response(200, 'Authentification réussie', $jeton);
    } else {
        usleep(400000); // petite attente pour ralentir les essais de mots de passe en série
        deliver_response(401, 'Identifiant ou mot de passe incorrect.');
    }
} catch (Throwable $e) {
    error_log('[auth] ' . $e->getMessage());
    deliver_response(500, 'Erreur interne du serveur.');
}
