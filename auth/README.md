# API d'authentification (`auth/`)

Connecte l'entraîneur et délivre un **jeton JWT** (JSON Web Token) : une chaîne signée qui prouve qui il est, valable un temps limité.
Les autres services ne connaissent jamais le mot de passe : ils présentent le jeton, et cette API dit s'il est valable.

## Configuration

Copier `.env.example` en `.env` (jamais envoyé sur Git) :

| Variable | Rôle |
|---|---|
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` | accès à la base d'authentification (`db/auth.sql`) |
| `JWT_SECRET` | secret qui signe les jetons, **32 caractères minimum**, propre à ce service |
| `JWT_TTL` | durée de vie d'un jeton en secondes (1800 par défaut) |
| `ALLOWED_ORIGIN` | sites autorisés à appeler l'API depuis un navigateur (vide = aucun) |

Compte de démonstration (base `db/auth.sql`) : identifiant `coach`, mot de passe `demo1234`.

## Utilisation

Adresse : `authapi.php`. Toutes les réponses ont la forme `{ "status_code", "status_message", "data" }`.

**Se connecter** (`POST`) :

```bash
curl -X POST http://127.0.0.1:8001/authapi.php -d '{"identifiant":"coach","password":"demo1234"}'
# -> { "status_code": 200, "status_message": "Authentification réussie", "data": "<jeton>" }
```

**Vérifier un jeton** (`GET`) : envoyer `Authorization: Bearer <jeton>`. Réponse 200 si le jeton est valable, 401 sinon.
C'est ce que fait l'API de gestion avant chaque requête.

| Code | Signification |
|---|---|
| 200 | connexion réussie / jeton valide |
| 400 | identifiant ou mot de passe absent ou invalide |
| 401 | identifiant ou mot de passe incorrect, jeton invalide ou expiré |
| 405 | méthode non autorisée |
| 500 | erreur interne (détail dans les journaux du serveur uniquement) |

## Sécurité

- Les mots de passe sont enregistrés **hachés** (`password_hash`) et comparés avec `password_verify`.
- Jeton HS256 : signature vérifiée avec `hash_equals`, algorithme imposé, expiration obligatoire (voir `jwt_utils.php`).
- Attente de 0,4 s après un échec de connexion ; aucune information technique dans les réponses d'erreur.
- Documentation interactive Swagger : `docs/` (fichier `openapi.yaml`).
