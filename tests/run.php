<?php
// Tests automatiques sans base de données ni serveur : jetons JWT, validation des données, protections de l'interface.
// Lancer :  php tests/run.php        (code de sortie 0 = tout est bon, 1 = au moins un test échoue)

$racine = dirname(__DIR__);
require_once $racine . '/auth/jwt_utils.php';
require_once $racine . '/api/Modeles/validation.php';
require_once $racine . '/web/src/Controleurs/config_api.php';

$reussis = 0;
$echecs = 0;

function verifier(string $nom, $condition): void
{
    global $reussis, $echecs;
    if ($condition) {
        $reussis++;
        echo "  ok     $nom\n";
    } else {
        $echecs++;
        echo "  ECHEC  $nom\n";
    }
}

$secret = str_repeat('a', 40);
$bloc = function ($donnees) {
    return base64url_encode(json_encode($donnees));
};

echo "Jetons JWT\n";
$jeton = generate_jwt(['id_entraineur' => 1, 'exp' => time() + 60], $secret);
verifier('un jeton frais est valide', is_jwt_valid($jeton, $secret));
verifier('le contenu du jeton est relu correctement', verifier_jwt($jeton, $secret)['id_entraineur'] === 1);
verifier('un mauvais secret est refusé', !is_jwt_valid($jeton, str_repeat('b', 40)));
verifier('un jeton expiré est refusé', !is_jwt_valid(generate_jwt(['exp' => time() - 5], $secret), $secret));
verifier('un jeton sans date d\'expiration est refusé', !is_jwt_valid(generate_jwt(['id' => 1], $secret), $secret));
[$e, $c, $s] = explode('.', $jeton);
verifier('un contenu modifié est refusé', !is_jwt_valid($e . '.' . $bloc(['id_entraineur' => 2, 'exp' => time() + 60]) . '.' . $s, $secret));
verifier('l\'algorithme « none » est refusé', !is_jwt_valid($bloc(['alg' => 'none']) . '.' . $c . '.', $secret));
verifier('un texte quelconque est refusé sans erreur', !is_jwt_valid('n-importe-quoi', $secret));
verifier('deux parties seulement : refusé', !is_jwt_valid('a.b', $secret));
verifier('du base64 invalide est refusé', !is_jwt_valid('!!!.???.***', $secret));
verifier('une valeur vide ou nulle est refusée', !is_jwt_valid('', $secret) && !is_jwt_valid(null, $secret));
verifier('un jeton trop long est refusé', !is_jwt_valid(str_repeat('a', 5000), $secret));

echo "\nValidation des données (API de gestion)\n";
verifier('entier positif : "12" et 12 acceptés', lire_entier_positif('12') === 12 && lire_entier_positif(12) === 12);
verifier('entier positif : 0, -1, "1e3", "12abc", tableau refusés',
    lire_entier_positif(0) === null && lire_entier_positif('-1') === null && lire_entier_positif('1e3') === null
    && lire_entier_positif('12abc') === null && lire_entier_positif([1]) === null);
verifier('texte : espaces retirés, longueur contrôlée', lire_texte('  Jean ', 3, 50) === 'Jean' && lire_texte('Al', 3, 50) === null && lire_texte(12, 1, 5) === null);
verifier('liste : insensible à la casse, renvoie la forme officielle', lire_valeur_liste('extérieur', LIEUX_MATCH) === 'Extérieur' && lire_valeur_liste('Ailleurs', LIEUX_MATCH) === null);
verifier('date : 2024-02-29 valide, 2023-02-29 refusée', lire_date('2024-02-29') === '2024-02-29' && lire_date('2023-02-29') === null && lire_date('29/02/2024') === null);
verifier('date et heure : 3 écritures acceptées',
    lire_date_heure('2030-05-01 18:30') === '2030-05-01 18:30:00' && lire_date_heure('2030-05-01T18:30') === '2030-05-01 18:30:00' && lire_date_heure('2030-05-01 18:30:45') === '2030-05-01 18:30:45');
verifier('date et heure : 25 h refusée', lire_date_heure('2030-05-01 25:30') === null);

$joueur = ['numero_licence' => '1234', 'nom' => 'Martin', 'prenom' => 'Lucas', 'date_naissance' => '2001-03-14', 'taille' => 188, 'poids' => 84, 'statut' => 'actif'];
[$erreur, $propre] = valider_joueur($joueur);
verifier('joueur valide accepté, statut remis en forme', $erreur === null && $propre['statut'] === 'Actif' && $propre['numero_licence'] === 1234);
verifier('licence absente refusée', valider_joueur(array_diff_key($joueur, ['numero_licence' => 1]))[0] !== null);
verifier('nom trop court refusé', valider_joueur(array_merge($joueur, ['nom' => 'Li']))[0] !== null);
verifier('taille de 30 cm refusée', valider_joueur(array_merge($joueur, ['taille' => 30]))[0] !== null);
verifier('naissance dans le futur refusée', valider_joueur(array_merge($joueur, ['date_naissance' => '2999-01-01']))[0] !== null);
verifier('statut « Supprimé » non acceptable depuis l\'extérieur', valider_joueur(array_merge($joueur, ['statut' => 'Supprimé']))[0] !== null);
verifier('taille et poids facultatifs', valider_joueur(array_merge($joueur, ['taille' => '', 'poids' => '']))[0] === null);

echo "\nInterface web\n";
verifier('e() neutralise le code injecté', e('<script>alert("x")</script>') === '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;');
verifier('e() protège aussi les apostrophes', e("l'été") === 'l&#039;été');
verifier('e() accepte nombres et null', e(12) === '12' && e(null) === '');
$contenu = base64_encode(json_encode(['exp' => 4102444800]));
verifier('date d\'expiration lue dans un jeton', expiration_du_jeton("x.$contenu.y") === 4102444800);
verifier('jeton illisible : expiration à 0', expiration_du_jeton('abc') === 0 && expiration_du_jeton(null) === 0);
$_SESSION['csrf'] = str_repeat('0', 64);
verifier('champ CSRF présent dans les formulaires', strpos(csrf_champ(), str_repeat('0', 64)) !== false);

echo "\nRésultat : $reussis réussis, $echecs échecs\n";
exit($echecs > 0 ? 1 : 0);
