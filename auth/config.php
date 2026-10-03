<?php
// Configuration du service d'authentification.
// Les réglages (accès à la base, secret du jeton...) ne sont PAS écrits dans le code :
// ils viennent des variables d'environnement, ou d'un fichier .env placé à côté de ce fichier
// (modèle : .env.example). Le fichier .env ne doit jamais être envoyé sur Git.

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
        // Retire des guillemets éventuels autour de la valeur
        if (strlen($valeur) >= 2 && ($valeur[0] === '"' || $valeur[0] === "'") && substr($valeur, -1) === $valeur[0]) {
            $valeur = substr($valeur, 1, -1);
        }
        // Une variable déjà définie dans l'environnement garde la priorité
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

charger_env(__DIR__ . '/.env');
