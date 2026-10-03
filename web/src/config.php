<?php
// Configuration de l'interface web : réglages lus dans l'environnement ou dans un fichier .env,
// session sécurisée, protection contre les failles XSS (e) et CSRF (csrf_*).
// Modèle de configuration : ../.env.example (le fichier .env ne doit jamais être envoyé sur Git).

function charger_env(string $fichier): void
{
    if (!is_readable($fichier)) {
        return;
    }
    foreach (file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $ligne) {
        $ligne = trim($ligne);
        if ($ligne === '' || $ligne[0] === '#' || strpos($ligne, '=') === false) {
            continue;
        }
        [$cle, $valeur] = explode('=', $ligne, 2);
        $cle = trim($cle);
        $valeur = trim($valeur);
        if (strlen($valeur) >= 2 && ($valeur[0] === '"' || $valeur[0] === "'") && substr($valeur, -1) === $valeur[0]) {
            $valeur = substr($valeur, 1, -1);
        }
        if (getenv($cle) === false) {
            putenv("$cle=$valeur");
        }
    }
}

function env(string $cle, ?string $defaut = null): ?string
{
    $valeur = getenv($cle);
    return ($valeur === false || $valeur === '') ? $defaut : $valeur;
}

charger_env(__DIR__ . '/../.env');

// Démarre la session avec des cookies mieux protégés (invisibles du JavaScript, non envoyés par d'autres sites)
function demarrer_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

// Protège l'affichage : transforme <, >, &, " et ' en texte inoffensif (contre l'injection de code dans la page)
function e($valeur): string
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Jeton secret propre à la session, ajouté à chaque formulaire pour refuser les envois venus d'un autre site (CSRF)
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_champ(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

// À appeler au début de tout traitement de formulaire : stoppe la requête si le jeton est absent ou faux
function csrf_verifier(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    $recu = $_POST['csrf'] ?? '';
    if (!is_string($recu) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $recu)) {
        http_response_code(403);
        exit('Requête refusée (formulaire expiré ou invalide). Retournez à la page précédente, actualisez-la puis recommencez.');
    }
}
