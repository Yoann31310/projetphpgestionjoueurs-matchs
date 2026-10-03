<?php
// Adresses des API et fonction d'appel commune à toute l'interface.
// Les adresses viennent de la configuration (voir ../config.php et .env.example).
require_once __DIR__ . '/../config.php';

$base_gestion = rtrim(env('GESTION_API_URL', 'http://127.0.0.1:8002'), '/');
define('urlApiAuthentification', env('AUTH_API_URL', 'http://127.0.0.1:8001/authapi.php'));
define('urlApiGestionJoueur', $base_gestion . '/apiGestionJoueur.php');
define('urlApiGestionMatch', $base_gestion . '/apiGestionMatch.php');
define('urlApiGestionFeuilleMatch', $base_gestion . '/apiGestionFeuilleMatch.php');
define('urlApiGestionStats', $base_gestion . '/apiGestionStats.php');

$GLOBALS['derniere_reponse_api'] = null;

// Envoie une requête à une API et renvoie sa réponse décodée (tableau), ou null si l'API est injoignable.
// Le jeton de connexion, gardé dans la session, est ajouté automatiquement.
function appel_api($methode, $url, $donnees = null)
{
    $ch = curl_init($url);
    $entetes = ['Accept: application/json'];
    if (isset($_SESSION['jwt'])) {
        $entetes[] = 'Authorization: Bearer ' . $_SESSION['jwt'];
    }
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $methode,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];
    if ($donnees !== null) {
        $json = json_encode($donnees);
        $options[CURLOPT_POSTFIELDS] = $json;
        $entetes[] = 'Content-Type: application/json';
        $entetes[] = 'Content-Length: ' . strlen($json);
    }
    $options[CURLOPT_HTTPHEADER] = $entetes;
    curl_setopt_array($ch, $options);

    $reponse = curl_exec($ch);
    $code_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $resultat = ($reponse === false) ? null : json_decode($reponse, true);
    $GLOBALS['derniere_reponse_api'] = is_array($resultat) ? $resultat : null;

    // Jeton refusé par une API de gestion : la session a expiré, on renvoie vers la page de connexion
    if ($code_http === 401 && $url !== urlApiAuthentification && isset($_SESSION['jwt'])) {
        terminer_session('Votre session a expiré, merci de vous reconnecter.');
    }
    return is_array($resultat) ? $resultat : null;
}

// Message d'erreur à montrer à l'utilisateur après un échec d'API :
// le message précis de l'API pour une erreur de saisie (4xx), un texte général sinon.
function message_erreur_api($message_par_defaut)
{
    $reponse = $GLOBALS['derniere_reponse_api'];
    if (is_array($reponse) && isset($reponse['status_code'], $reponse['status_message'])
        && $reponse['status_code'] >= 400 && $reponse['status_code'] < 500) {
        return $reponse['status_message'];
    }
    return $message_par_defaut;
}

// Ferme la session et renvoie vers la page de connexion avec un message
function terminer_session($message = null)
{
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    if ($message !== null) {
        $_SESSION['erreur'] = $message;
    }
    header('Location: ../Vues/PageConnexion.php');
    exit();
}

// Lit la date d'expiration inscrite dans le jeton (sans en vérifier la signature :
// c'est le rôle de l'API d'authentification, interrogée par les API de gestion)
function expiration_du_jeton($jwt)
{
    $parties = explode('.', (string) $jwt);
    if (count($parties) !== 3) {
        return 0;
    }
    $contenu = json_decode(base64_decode(strtr($parties[1], '-_', '+/')), true);
    return (is_array($contenu) && isset($contenu['exp'])) ? (int) $contenu['exp'] : 0;
}

// Page réservée : redirige vers la connexion si l'entraîneur n'est pas connecté ou si son jeton a expiré
function verifier_authentification()
{
    if (empty($_SESSION['jwt'])) {
        header('Location: ../Vues/PageConnexion.php');
        exit();
    }
    if (expiration_du_jeton($_SESSION['jwt']) < time()) {
        terminer_session('Votre session a expiré, merci de vous reconnecter.');
    }
    return true;
}
