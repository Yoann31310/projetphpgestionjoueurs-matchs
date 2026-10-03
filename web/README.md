# Interface web (`web/`)

Pages utilisées par l'entraîneur. L'interface ne touche jamais la base de données : elle appelle les API (`auth/` pour la connexion, `api/` pour les données).
Le dossier à exposer sur le serveur web est **`web/src/`**.

## Configuration

Copier `.env.example` en `.env` (dans `web/`, hors du dossier `src`) :

| Variable | Rôle |
|---|---|
| `AUTH_API_URL` | adresse de l'API d'authentification |
| `GESTION_API_URL` | adresse de base de l'API de gestion |

## Fonctionnalités

1. **Connexion** : identifiant et mot de passe vérifiés par l'API d'authentification. Le jeton reçu est gardé dans la session et envoyé à chaque appel.
2. **Tableau de bord** : nombre de joueurs actifs, prochain match, dernier résultat.
3. **Joueurs** : liste, ajout, modification, suppression logique (impossible si le joueur a déjà joué).
4. **Matchs** : calendrier, programmation, modification, suppression (matchs à venir), saisie du résultat (matchs joués).
5. **Feuille de match** : choix des titulaires (5 à 7) et remplaçants (7 maximum) parmi les joueurs actifs, avec leur poste.
6. **Évaluation** : note de 0 à 10 et commentaire pour chaque joueur après le match.
7. **Statistiques** : victoires, défaites, nuls, taux de victoire, âge moyen, participations par joueur.

## Organisation du code

- `src/Controleurs/` : un contrôleur par page ; `config_api.php` contient l'appel aux API.
- `src/Modeles/Classes/` : objets métier qui appellent les API (plus aucun accès direct à la base).
- `src/Vues/` : pages HTML/PHP ; toutes les valeurs affichées passent par `e()`, tous les formulaires contiennent un jeton CSRF.
- `src/config.php` : lecture de la configuration, session sécurisée, fonctions `e()` et `csrf_*()`.

Si le jeton expire (30 minutes par défaut), l'interface renvoie à la page de connexion avec un message.
