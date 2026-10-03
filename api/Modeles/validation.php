<?php
// Fonctions de vérification des données reçues par les API.
// Chaque fonction renvoie la valeur nettoyée, ou null si elle n'est pas acceptable.

const STATUTS_JOUEUR = ['Actif', 'Blessé', 'Absent', 'Suspendu'];
const LIEUX_MATCH = ['Domicile', 'Extérieur'];
const RESULTATS_MATCH = ['Gagnée', 'Perdue', 'Égalité'];
const ROLES_FEUILLE = ['titulaire', 'remplaçant'];
const POSTES_FEUILLE = ['Gardien', 'Pivot', 'Demi-centre', 'Arrière gauche', 'Arrière droit', 'Ailier gauche', 'Ailier droit'];

// Entier strictement positif (accepte "12" ou 12), sinon null
function lire_entier_positif($valeur)
{
    if (is_int($valeur)) {
        return $valeur > 0 ? $valeur : null;
    }
    if (is_string($valeur) && preg_match('/^[1-9][0-9]{0,9}$/', $valeur)) {
        return (int) $valeur;
    }
    return null;
}

// Texte sans espaces autour, de longueur comprise entre $min et $max (en caractères), sinon null
function lire_texte($valeur, $min, $max)
{
    if (!is_string($valeur)) {
        return null;
    }
    $texte = trim($valeur);
    $longueur = mb_strlen($texte, 'UTF-8');
    return ($longueur >= $min && $longueur <= $max) ? $texte : null;
}

// Cherche une valeur dans une liste sans tenir compte des majuscules ; renvoie la forme officielle
function lire_valeur_liste($valeur, array $autorisees)
{
    if (!is_string($valeur)) {
        return null;
    }
    foreach ($autorisees as $officielle) {
        if (mb_strtolower($officielle, 'UTF-8') === mb_strtolower(trim($valeur), 'UTF-8')) {
            return $officielle;
        }
    }
    return null;
}

// Date au format AAAA-MM-JJ valide, sinon null
function lire_date($valeur)
{
    if (!is_string($valeur) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $valeur)) {
        return null;
    }
    $date = DateTime::createFromFormat('Y-m-d', $valeur);
    return ($date && $date->format('Y-m-d') === $valeur) ? $valeur : null;
}

// Date et heure (AAAA-MM-JJ HH:MM[:SS] ou AAAA-MM-JJTHH:MM), renvoyée sous la forme "AAAA-MM-JJ HH:MM:SS"
function lire_date_heure($valeur)
{
    if (!is_string($valeur) || !preg_match('/^(\d{4}-\d{2}-\d{2})[ T](\d{2}:\d{2})(:\d{2})?$/', trim($valeur), $m)) {
        return null;
    }
    $secondes = $m[3] ?? ':00';
    $texte = $m[1] . ' ' . $m[2] . $secondes;
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $texte);
    return ($date && $date->format('Y-m-d H:i:s') === $texte) ? $texte : null;
}

// Vérifie les données d'un joueur. Renvoie [message d'erreur ou null, joueur propre].
// Nom, prénom et licence sont obligatoires ; taille, poids, naissance et statut sont facultatifs.
function valider_joueur(array $data)
{
    $propre = [];

    $propre['numero_licence'] = lire_entier_positif($data['numero_licence'] ?? null);
    if ($propre['numero_licence'] === null) {
        return ['Le numéro de licence est obligatoire (nombre entier positif).', null];
    }
    $propre['nom'] = lire_texte($data['nom'] ?? null, 3, 50);
    $propre['prenom'] = lire_texte($data['prenom'] ?? null, 3, 50);
    if ($propre['nom'] === null || $propre['prenom'] === null) {
        return ['Le nom et le prénom sont obligatoires (entre 3 et 50 caractères).', null];
    }

    $propre['date_naissance'] = null;
    if (isset($data['date_naissance']) && $data['date_naissance'] !== '') {
        $propre['date_naissance'] = lire_date($data['date_naissance']);
        if ($propre['date_naissance'] === null || $propre['date_naissance'] > date('Y-m-d')) {
            return ['La date de naissance doit être une date passée au format AAAA-MM-JJ.', null];
        }
    }

    $propre['taille'] = null;
    if (isset($data['taille']) && $data['taille'] !== '') {
        $taille = lire_entier_positif($data['taille']);
        if ($taille === null || $taille < 80 || $taille > 250) {
            return ['La taille doit être comprise entre 80 et 250 cm.', null];
        }
        $propre['taille'] = $taille;
    }

    $propre['poids'] = null;
    if (isset($data['poids']) && $data['poids'] !== '') {
        $poids = lire_entier_positif($data['poids']);
        if ($poids === null || $poids < 20 || $poids > 250) {
            return ['Le poids doit être compris entre 20 et 250 kg.', null];
        }
        $propre['poids'] = $poids;
    }

    $propre['statut'] = 'Actif';
    if (isset($data['statut']) && $data['statut'] !== '') {
        $propre['statut'] = lire_valeur_liste($data['statut'], STATUTS_JOUEUR);
        if ($propre['statut'] === null) {
            return ['Statut invalide. Valeurs possibles : ' . implode(', ', STATUTS_JOUEUR) . '.', null];
        }
    }
    return [null, $propre];
}
