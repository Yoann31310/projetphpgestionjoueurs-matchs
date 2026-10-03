<?php
// Outils pour fabriquer et vérifier un jeton JWT (algorithme HS256).
// Un jeton est composé de trois parties séparées par des points : en-tête.contenu.signature

function base64url_encode($donnees)
{
    return rtrim(strtr(base64_encode($donnees), '+/', '-_'), '=');
}

// Décodage strict : renvoie false si la chaîne n'est pas du base64 valide
function base64url_decode($texte)
{
    if (!is_string($texte) || $texte === '' || preg_match('/[^A-Za-z0-9_-]/', $texte)) {
        return false;
    }
    $reste = strlen($texte) % 4;
    if ($reste === 1) {
        return false;
    }
    if ($reste > 0) {
        $texte .= str_repeat('=', 4 - $reste);
    }
    return base64_decode(strtr($texte, '-_', '+/'), true);
}

// Fabrique un jeton signé. Le contenu (payload) doit contenir une date d'expiration 'exp'.
function generate_jwt(array $payload, string $secret): string
{
    $entete = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $contenu = base64url_encode(json_encode($payload));
    $signature = base64url_encode(hash_hmac('sha256', "$entete.$contenu", $secret, true));
    return "$entete.$contenu.$signature";
}

// Vérifie un jeton : signature correcte, algorithme attendu, date d'expiration non dépassée.
// Renvoie le contenu du jeton (tableau) s'il est valide, sinon null. Ne déclenche jamais d'erreur PHP.
function verifier_jwt($jwt, string $secret)
{
    if (!is_string($jwt) || strlen($jwt) > 4096) {
        return null;
    }
    $parties = explode('.', $jwt);
    if (count($parties) !== 3) {
        return null;
    }
    [$entete_b64, $contenu_b64, $signature_fournie] = $parties;

    $entete = json_decode((string) base64url_decode($entete_b64), true);
    if (!is_array($entete) || ($entete['alg'] ?? null) !== 'HS256') {
        return null; // refuse notamment l'algorithme "none"
    }

    $signature_attendue = base64url_encode(hash_hmac('sha256', "$entete_b64.$contenu_b64", $secret, true));
    if (!hash_equals($signature_attendue, $signature_fournie)) {
        return null;
    }

    $contenu = json_decode((string) base64url_decode($contenu_b64), true);
    if (!is_array($contenu) || !isset($contenu['exp']) || !is_int($contenu['exp']) || $contenu['exp'] < time()) {
        return null;
    }
    return $contenu;
}

function is_jwt_valid($jwt, string $secret): bool
{
    return verifier_jwt($jwt, $secret) !== null;
}

// Lit l'en-tête HTTP "Authorization"
function get_authorization_header()
{
    if (isset($_SERVER['Authorization'])) {
        return trim($_SERVER['Authorization']);
    }
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['HTTP_AUTHORIZATION']);
    }
    if (function_exists('apache_request_headers')) {
        foreach (apache_request_headers() as $nom => $valeur) {
            if (strtolower($nom) === 'authorization') {
                return trim($valeur);
            }
        }
    }
    return null;
}

// Extrait le jeton d'un en-tête "Authorization: Bearer <jeton>"
function get_bearer_token()
{
    $en_tete = get_authorization_header();
    if (!empty($en_tete) && preg_match('/^Bearer\s+(\S+)$/i', $en_tete, $correspondances)) {
        return $correspondances[1] === 'null' ? null : $correspondances[1];
    }
    return null;
}
