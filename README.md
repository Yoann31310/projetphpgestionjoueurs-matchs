# Gestion d'équipe de handball (projet R4.01)

Application web pour un entraîneur : gérer ses **joueurs**, programmer ses **matchs**, composer la **feuille de match**
(titulaires, remplaçants, postes), noter les joueurs après la rencontre et consulter des **statistiques**.

Le projet est découpé en trois applications indépendantes qui communiquent par des API REST :

```
 Navigateur ──► web  (interface, port 8003)
                 │  1. connexion              ──► auth (API d'authentification, port 8001)  ──► base « auth »
                 │  2. données (avec le jeton) ──► api  (API de gestion, port 8002) ──► base « gestion »
                                                    │  vérifie chaque jeton auprès de auth
```

| Dossier | Rôle | Documentation |
|---|---|---|
| [`auth/`](auth/README.md) | Connexion de l'entraîneur, fabrique et vérifie les jetons JWT | Swagger : `auth/docs/` |
| [`api/`](api/README.md) | Joueurs, matchs, feuilles de match, statistiques (réservé aux utilisateurs connectés) | Swagger : `api/docs/` |
| [`web/`](web/README.md) | Interface utilisée par l'entraîneur (pages PHP) | — |
| [`db/`](db/) | Structure des deux bases + données factices | `auth.sql`, `gestion.sql` |
| [`tests/`](tests/run.php) | Tests automatiques | `php tests/run.php` |

Ces trois applications étaient auparavant trois dépôts séparés (`auth_api`, `projetphpgestionjoueurs-matchs`, `projetphp`).

## Installer et lancer en local

Prérequis : PHP 8.1 ou plus (extensions `pdo_mysql`, `curl`, `mbstring`) et MySQL ou MariaDB.

1. **Créer les deux bases et les remplir** (données factices) :
   ```bash
   mysql -u root -p -e "CREATE DATABASE equipe_sport_auth CHARACTER SET utf8mb4; CREATE DATABASE equipe_sport_gestion CHARACTER SET utf8mb4;"
   mysql -u root -p equipe_sport_auth     < db/auth.sql
   mysql -u root -p equipe_sport_gestion  < db/gestion.sql
   ```
2. **Créer les fichiers de configuration** : dans `auth/`, `api/` et `web/`, copier `.env.example` en `.env` et renseigner les valeurs
   (accès à la base, et dans `auth/.env` un `JWT_SECRET` long et aléatoire :
   `php -r "echo bin2hex(random_bytes(32));"`). Ces fichiers `.env` ne sont jamais envoyés sur Git.
3. **Démarrer** : `scripts/demarrer-local.sh` (Linux/macOS) ou `scripts/demarrer-local.ps1` (Windows).
4. Ouvrir <http://127.0.0.1:8003> et se connecter avec le compte de démonstration :
   identifiant **`coach`**, mot de passe **`demo1234`** (compte factice, à supprimer ou changer en production).

Pour un vrai serveur (Apache ou Nginx), pointer un hôte vers `auth/`, un vers `api/` et un vers `web/src/`, puis adapter `AUTH_API_URL`,
`GESTION_API_URL` et `ALLOWED_ORIGIN` dans les `.env`. Chaque dossier contient un `.htaccess` qui interdit l'accès aux fichiers internes.

## Tests et intégration continue

- `php tests/run.php` : 32 tests sans base de données (jetons JWT, validation des données, protections de l'interface).
- `.gitlab-ci.yml` : à chaque envoi sur GitLab, trois vérifications : syntaxe PHP de tous les fichiers, tests ci-dessus,
  et absence de mot de passe écrit en clair dans le code.
  Le pipeline n'utilise ni Docker-in-Docker ni « Auto DevOps » (qui échouaient sur le serveur de l'IUT).

## Règles de gestion principales

- Un joueur « supprimé » reste en base (suppression logique) pour garder les statistiques ; il n'est plus affiché ni sélectionnable.
- Un joueur ne peut pas être supprimé s'il a déjà été sélectionné dans un match.
- Statuts possibles : Actif, Blessé, Absent, Suspendu. Seuls les joueurs actifs peuvent figurer sur une feuille de match.
- Feuille de match : 5 à 7 titulaires, 7 remplaçants au maximum, un poste par joueur, aucun joueur en double.
- Un match à venir peut être modifié ou supprimé ; un match joué ne peut plus changer (sauf son résultat) et reçoit les notes (0 à 10).

## Sécurité

Le détail des failles corrigées et de ce qui reste à améliorer est dans [`docs/audit-securite.md`](docs/audit-securite.md).
