# Audit rapide : sécurité, fonctionnement, documentation

Relecture des trois dépôts d'origine (`auth_api`, `projetphpgestionjoueurs-matchs`, `projetphp`) avant leur fusion.
Chaque point ci-dessous est corrigé dans ce dépôt, sauf la section « Reste à améliorer ».

## Failles de sécurité corrigées

| # | Problème trouvé | Risque | Correction |
|---|---|---|---|
| 1 | Identifiants de base de données écrits dans le code **et dans les README** (publics sur Git) | Quiconque lit le dépôt peut ouvrir les bases | Configuration par variables d'environnement / fichier `.env` (ignoré par Git, modèle `.env.example`). Une vérification CI refuse tout mot de passe en clair. **Les anciens mots de passe doivent être changés** : ils restent dans l'historique des anciens dépôts. |
| 2 | Secret des jetons JWT = `random`, copié dans l'interface | N'importe qui peut fabriquer un faux jeton | Secret long (32 caractères minimum) lu dans `JWT_SECRET`, connu **uniquement** du service d'authentification. L'interface ne vérifie plus la signature : les API de gestion interrogent l'API d'authentification. |
| 3 | Vérification du jeton fragile (`explode`/`base64_decode` sans contrôle, comparaison non sécurisée, algorithme non vérifié) | Erreurs PHP sur un jeton mal formé, jeton « none » ou forgé | Vérification stricte : trois parties, algorithme HS256 imposé, signature comparée avec `hash_equals`, expiration obligatoire. Testé (`tests/run.php`). |
| 4 | Contenu affiché sans protection (nom d'adversaire, commentaires, notes…) | Injection de code dans la page (XSS stockée) | Toutes les valeurs affichées passent par `e()` (htmlspecialchars). |
| 5 | Formulaires sans jeton anti-CSRF | Un site tiers peut déclencher une action au nom de l'entraîneur connecté | Jeton secret par session dans chaque formulaire, vérifié par chaque contrôleur. |
| 6 | Session : identifiant de session conservé à la connexion, cookie lisible par le JavaScript | Vol ou fixation de session | `session_regenerate_id` à la connexion, cookie `HttpOnly` + `SameSite=Lax`. |
| 7 | `Access-Control-Allow-Origin: *` sur les API | Tout site peut appeler l'API depuis un navigateur | Origines autorisées listées dans `ALLOWED_ORIGIN` (aucune par défaut). |
| 8 | `die("Erreur : " . $e->getMessage())` | Fuite du détail technique (adresse de la base, requêtes) | Détail écrit dans les journaux du serveur, réponse générique au client. |
| 9 | Essais de mots de passe sans frein, entrées non contrôlées | Force brute, erreurs inattendues | Attente de 0,4 s après un échec, types et longueurs vérifiés. |
| 10 | Appel de l'API d'authentification sans délai limite ni vérification de certificat explicite | Blocage du serveur si l'API ne répond pas | Délais de connexion et de réponse, certificat vérifié, pas de redirection suivie. |
| 11 | Fichiers internes (`.env`, `config.php`, DAO, SQL) accessibles par URL sur Apache | Lecture de la configuration | `.htaccess` dans chaque dossier. |
| 12 | Mot de passe d'un compte créé par le DAO enregistré tel quel | Mots de passe en clair si le compte est créé sans hachage | Le DAO hache toujours le mot de passe. |

## Fonctions qui ne marchaient pas (ou mal) corrigées

| Problème | Correction |
|---|---|
| **Saisir le résultat d'un match déjà joué était impossible** dans l'interface : le contrôleur refusait la date « dans le passé » avant même d'appeler l'API | Un match joué garde sa date ; seul le résultat est envoyé. Testé de bout en bout. |
| Ouvrir le détail d'un match inexistant provoquait une erreur fatale PHP | Redirection avec un message. |
| Les messages précis de l'API (« licence déjà attribuée », « joueur déjà sélectionné »…) n'étaient jamais montrés : l'interface affichait « Échec via l'API » | Le message de l'API est affiché quand il s'agit d'une erreur de saisie. |
| La modification d'un joueur (`PUT`) ne faisait pas les contrôles de la création (taille, poids, longueur du nom…) | Mêmes règles pour la création et la modification. |
| Corps de requête invalide ou champ manquant : erreur PHP (`TypeError`) au lieu d'une réponse claire | Réponse 400 avec un message ; paramètres `id` contrôlés (entiers). |
| Enregistrer une feuille de match supprimait l'ancienne puis réinsérait : un échec en cours de route la perdait ; un joueur pouvait figurer deux fois ; rôle et poste non vérifiés | Enregistrement en une seule transaction, doublons et valeurs interdits. |
| Noter un joueur absent de la feuille répondait « Évaluation enregistrée » sans rien enregistrer ; notes hors 0-10 acceptées | 404 si le joueur n'est pas sur la feuille, note contrôlée. |
| Statut, lieu et résultat acceptaient n'importe quel texte | Listes de valeurs autorisées. |
| La sélection de la feuille de match proposait des joueurs blessés, absents ou suspendus, que l'API refusait ensuite | Seuls les joueurs « Actif » sont proposés. |
| Une ancienne page d'évaluation, jamais reliée au menu, avait un formulaire incompatible avec son contrôleur (rien ne s'enregistrait) | Page supprimée : l'évaluation se fait dans la page du match, qui fonctionne (notes décimales conservées, par exemple 7,5). |
| Table `Participer` sans clé primaire (doublons possibles) | Clé primaire (match, joueur). |
| Pas d'avertissement PHP en cas de champ de formulaire absent | Valeurs par défaut sur tous les champs lus. |

## Documentation et données

- README racine + un README par service (installation, configuration, endpoints) ; plus aucun identifiant dans les README.
- `db/auth.sql` et `db/gestion.sql` : structure propre (clés, contraintes) et **données factices** (15 joueurs, 6 matchs, feuilles de match, notes, 1 compte de démonstration). Régénérables avec `python db/generer_sql.py`.
- Documentation Swagger (OpenAPI) conservée dans `auth/docs` et `api/docs`.
- Tests automatiques et pipeline GitLab.

## Reste à améliorer (non fait)

- Limiter vraiment le nombre d'essais de connexion (par adresse IP) avec un stockage partagé.
- Passer en HTTPS partout et envoyer les jetons dans un cookie sécurisé plutôt que dans la session PHP.
- Révocation d'un jeton avant son expiration (liste noire) et jeton de renouvellement.
- Journalisation structurée des actions (qui a modifié quoi).
- Tests automatiques sur la base de données (aujourd'hui testée à la main, voir `tests/` pour les tests sans base).
- Dépendances : le projet n'en utilise aucune (PHP seul) ; si `composer` est ajouté un jour, y activer `composer audit` dans la CI.
