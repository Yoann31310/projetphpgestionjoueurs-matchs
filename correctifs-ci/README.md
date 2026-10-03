# Correctif de la CI GitLab de `projetprofphp`

## Ce qui échouait

Le projet n'avait pas de fichier `.gitlab-ci.yml` : GitLab a donc lancé **Auto DevOps**, qui a échoué sur son job `build`
(et fait sauter les jobs `test`, `code_quality`, `container_scanning`, `secret_detection` et `semgrep-sast`, qui en dépendent).
Deux causes, deux messages dans le journal :

| Message | Cause |
|---|---|
| `ERROR: Failed to parse 'composer.lock'` / `No 'composer.json' found!` | Auto DevOps essaie de construire l'application avec un « buildpack » Heroku PHP, qui exige Composer. Le projet n'utilise pas Composer. |
| `error during connect: ... lookup docker ... server misbehaving` | Auto DevOps veut construire une image Docker avec Docker-in-Docker, un service que les exécuteurs (runners) de l'IUT ne fournissent pas. |

Ce ne sont pas des erreurs dans le code PHP : le projet n'a rien à construire.

## Correction

Ajouter le fichier [`projetprofphp/.gitlab-ci.yml`](projetprofphp/.gitlab-ci.yml) **à la racine** du dépôt `projetprofphp`. Dès qu'il existe, GitLab n'utilise plus Auto DevOps.
Il définit deux jobs rapides, sans Docker :

1. `syntaxe-php` : `php -l` sur tous les fichiers PHP (image officielle `php:8.2-cli`) ;
2. `fichiers-essentiels` : vérifie que `index.php`, `schema.sql`, `README.md`, `Psr4AutoloaderClass.php` et `stylesheet.css` sont présents.

```bash
git clone https://gitlab.info.iut-tlse3.fr/lln5097a/projetprofphp.git
cd projetprofphp
cp <chemin>/correctifs-ci/projetprofphp/.gitlab-ci.yml .
git add .gitlab-ci.yml
git commit -m "ci: pipeline simple (syntaxe PHP) à la place d'Auto DevOps"
git push
```

Le pipeline suivant doit afficher deux jobs verts (étape « verifier »). Si GitLab propose encore Auto DevOps, le désactiver dans
*Settings > CI/CD > Auto DevOps* (décocher « Default to Auto DevOps pipeline »).

## Vérifications faites avant de livrer

- Le fichier est un YAML valide ; chaque ligne de script est une commande.
- Les deux jobs ont été rejoués à la main sur une copie du dépôt : tous les fichiers PHP sont valides et les fichiers essentiels présents (sortie 0) ; avec `schema.sql` retiré, le job 2 échoue comme prévu.
- **Non vérifié** : l'exécution réelle sur le serveur GitLab de l'IUT (l'image `php:8.2-cli` doit pouvoir être téléchargée par l'exécuteur ; elle l'est normalement depuis Docker Hub).

## Même principe pour le projet fusionné

Le fichier [`../.gitlab-ci.yml`](../.gitlab-ci.yml) du dépôt `equipe-sport` suit la même logique avec trois jobs : syntaxe PHP, tests (`php tests/run.php`) et recherche de mots de passe écrits en clair.
