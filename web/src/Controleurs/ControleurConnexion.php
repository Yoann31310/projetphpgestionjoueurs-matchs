<?php
require_once __DIR__ . '/../config.php';
demarrer_session();

require_once 'config_api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: ../Vues/PageConnexion.php');
	exit();
}

csrf_verifier(); // refuse les formulaires venant d'un autre site

$identifiant_saisi = trim($_POST['identifiant'] ?? '');
$mot_de_passe_saisi = $_POST['mdp'] ?? '';

if ($identifiant_saisi === '' || $mot_de_passe_saisi === '') {
	$_SESSION['erreur'] = "Merci de saisir votre identifiant et votre mot de passe.";
	header('Location: ../Vues/PageConnexion.php');
	exit();
}

// On appelle l'API d'authentification
$resultat = appel_api('POST', urlApiAuthentification, [
	'identifiant' => $identifiant_saisi,
	'password' => $mot_de_passe_saisi
]);

if (is_array($resultat) && ($resultat['status_code'] ?? 0) === 200 && !empty($resultat['data'])) {
	// Le jeton JWT est dans $resultat['data'] : on lit son contenu pour récupérer le nom et le prénom
	$jwt = $resultat['data'];
	$parties = explode('.', $jwt);
	$contenu = json_decode(base64_decode(strtr($parties[1] ?? '', '-_', '+/')), true);

	if (is_array($contenu) && isset($contenu['id_entraineur'])) {
		session_regenerate_id(true); // nouvelle session à la connexion (contre le vol de session)
		$_SESSION['id_entraineur'] = $contenu['id_entraineur'];
		$_SESSION['nom_entraineur'] = $contenu['nom'] ?? '';
		$_SESSION['prenom_entraineur'] = $contenu['prenom'] ?? '';
		$_SESSION['jwt'] = $jwt; // gardé pour les prochains appels aux API

		header('Location: ../Controleurs/ControleurAccueil.php');
		exit();
	}
}

// Échec : message de l'API pour une erreur de saisie, message général si l'API est injoignable
if (is_array($resultat) && isset($resultat['status_code']) && $resultat['status_code'] < 500) {
	$_SESSION['erreur'] = $resultat['status_message'] ?? "Connexion impossible.";
} else {
	$_SESSION['erreur'] = "Le service de connexion ne répond pas. Réessayez dans quelques instants.";
}
header('Location: ../Vues/PageConnexion.php');
exit();
