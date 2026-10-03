# API de gestion (`api/`)

Cœur métier : **joueurs, matchs, feuilles de match, statistiques**. Toutes les routes exigent un jeton valide, vérifié auprès de l'API d'authentification.

## Configuration

Copier `.env.example` en `.env` (jamais envoyé sur Git) :

| Variable | Rôle |
|---|---|
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` | accès à la base de gestion (`db/gestion.sql`) |
| `AUTH_API_URL` | adresse de l'API d'authentification (ex. `http://127.0.0.1:8001/authapi.php`) |
| `ALLOWED_ORIGIN` | sites autorisés à appeler l'API depuis un navigateur (vide = aucun) |

## Routes

Toutes les requêtes portent l'en-tête `Authorization: Bearer <jeton>` (jeton obtenu auprès de l'API d'authentification).
Les données sont envoyées et reçues en JSON : `{ "status_code", "status_message", "data" }`.

| Route | Méthode | Rôle |
|---|---|---|
| `apiGestionJoueur.php` | GET | joueurs actifs |
| `apiGestionJoueur.php?id=12` | GET / PUT / DELETE | un joueur : lire, modifier, supprimer (suppression logique) |
| `apiGestionJoueur.php` | POST | créer un joueur (`numero_licence`, `nom`, `prenom` obligatoires ; `date_naissance`, `taille`, `poids`, `statut` facultatifs) |
| `apiGestionJoueur.php?id=12` | POST | ajouter un commentaire (`commentaire`, 50 caractères maximum) |
| `apiGestionMatch.php` | GET / POST | liste des matchs / programmer un match (`date_heure`, `nom_equipe_adverse`, `lieu`, `adresse`) |
| `apiGestionMatch.php?id=3` | GET / PUT / DELETE | un match : lire, modifier ou saisir le résultat, supprimer |
| `apiGestionFeuilleMatch.php?id_match=3` | GET | participants d'un match |
| `apiGestionFeuilleMatch.php` | POST | enregistrer la feuille (`id_match`, `participants` : joueur, rôle, poste) |
| `apiGestionFeuilleMatch.php?id_match=3&id_joueur=5` | PUT / DELETE | changer rôle/poste ou noter le joueur (0 à 10) / le retirer de la feuille |
| `apiGestionStats.php` | GET | statistiques globales et par joueur |
| `apiGestionStats.php?id_joueur=5` | GET | sélections consécutives d'un joueur |

Valeurs autorisées : statut = Actif, Blessé, Absent, Suspendu ; lieu = Domicile, Extérieur ; résultat = Gagnée, Perdue, Égalité ;
rôle = titulaire, remplaçant ; postes = Gardien, Pivot, Demi-centre, Arrière gauche, Arrière droit, Ailier gauche, Ailier droit.

Exemple :

```bash
TOKEN=$(curl -s -X POST http://127.0.0.1:8001/authapi.php -d '{"identifiant":"coach","password":"demo1234"}' | python -c "import sys,json;print(json.load(sys.stdin)['data'])")
curl -H "Authorization: Bearer $TOKEN" http://127.0.0.1:8002/apiGestionJoueur.php
```

| Code | Signification |
|---|---|
| 200 / 201 | succès / création réussie |
| 400 | donnée invalide ou manquante (le message dit laquelle) |
| 401 | jeton absent, invalide ou expiré |
| 403 | action interdite par une règle de gestion (match déjà joué, joueur déjà sélectionné…) |
| 404 | joueur ou match introuvable |
| 409 | conflit (licence déjà attribuée, doublon) |
| 500 | erreur interne (détail dans les journaux du serveur uniquement) |

Documentation interactive Swagger : `docs/` (fichier `openapi.yaml`). Le plan de conception d'origine est dans [`../docs/plan-d-attaque-api.md`](../docs/plan-d-attaque-api.md).

## Organisation du code

- `apiGestion.php` : socle commun (réponses JSON, CORS, vérification du jeton, lecture sécurisée du corps de requête).
- `Modeles/validation.php` : contrôle de toutes les données reçues.
- `Modeles/DAO/` : requêtes SQL (toutes préparées) ; `Modeles/Classes/` : objets métier.
