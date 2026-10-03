> Document de conception d'origine, conservé pour mémoire. Pour l'organisation actuelle, voir le [README racine](../README.md).

# Plan d'Attaque

Voici comment nous avons pensé la suite de ces apis :

## `apiGestion.php` : le refactoring : 
Nous avons d'abord préparé un fichier `apiGestion.php` qui serait un refactoring de chaque api de ce repo. 
- Elle contient une fonction deliver_response() qui peut être utilisé par tout le monde pour envoyer un code / Message / Données en fonction du besoin. La fonction se charge elle même des headers, d'encoder et d'envoyer la réponse
- Elle contient tout ce qui est nécéssaire auprès de la vérification du jeton JWT de l'authentification. Car oui, toutes ces apis DOIVENT faire en sorte de répondre aux requêtes seulement si on leur donne un jeton valide et non expiré. On vérifie le jeton en faisant des appels à l'api d'authentification.

> On rappelle le lien de l'api d'authentification : `http://127.0.0.1:8001/authapi.php` (en local, voir README)

Le refactoring pour envoyer les réponses et pour décoder les jsons, et aussi vérifier que l'entraineur est connecté (en faisant des appels à l'api de connexion)

## A retenir d'important
- Validation JWT : Chaque appel aux APIs de gestion est intercepté par `apiGestion.php` (Backend) qui vérifie l'authenticité et la validité du jeton envoyé dans le header.
- Signature JWT : seul le service d'authentification connaît le secret (variable `JWT_SECRET`) ; les autres services lui demandent de vérifier les jetons.

- Comme prévu, dans ce repo se trouve les DAO, car on utilise des classes PHP (`Joueur`, `Matchs`, `Participation`) dans la partie frontend, qui font appel aux DAO pour récupérer les données, via les api présentes dans ce repo.



## Les gestions des requêtes selon les api 
> Voici un "tableau" qui représente le type de requête, relié à quelle opération se fait. 
> On aurait pu ne pas mettre l'api joueur et match, car c'était assez évident, il n'y a pas d'ambiguité.

### API Joueurs (`apiGestionJoueur.php`)
- GET           : Récupération des joueurs actifs.              JoueurDAO::recuperer_actifs()
- GET + id      : Récupération d'un joueur par son ID.          JoueurDAO::trouver_par_id($id)
- POST          : Création d'un nouveau joueur.                 JoueurDAO::ajouter($joueur)
- PUT + id      : Mise à jour complète du joueur.               JoueurDAO::modifier($id, $joueur)
- DELETE + id   : Suppression d'un joueur.                      JoueurDAO::supprimer($id)

> L'api fait des vérification en BD, et vérifie également certaines règles de notre projet, notamment le fait qu'un joueur au statut "Supprimé" est exclu des listes actives et ne peut plus participer à de nouveaux matchs. On rappelle que dans notre projet, supprimer un joueur ne le supprime pas, mais n'est juste plus affiché, pour pouvoir continuer à avoir des statistiques et des matchs valides dans la BD

### API Matchs (`apiGestionMatch.php`)
- GET           : Récupération de tous les matchs.              MatchDAO::recuperer_tout()
- GET + id      : Récupération d'un match par son ID.           MatchDAO::trouver_par_id($id)
- POST          : Programmation d'un nouveau match.             MatchDAO::ajouter($match)
- PUT + id      : Mise à jour d'un match (et du résultat).      MatchDAO::modifier($id, $match)
- DELETE + id   : Suppression d'un match.                       MatchDAO::supprimer($id)

> Cette api a aussi des vérifications en BD, et vérifie également que la mise à jour d'un résultat de match n'est possible que si le match a eu lieu.

### API Feuilles de Match (`apiGestionFeuilleMatch.php`)
- GET + id_match      : Lister les participants d'un match.                     ParticipationDAO::recuperer_participants()
- POST                : Ajouter un participant (id_j, role, poste).             ParticipationDAO::ajouter_participant()
- DELETE + id_match   : Vider la feuille de match.                              ParticipationDAO::vider_feuille()
- PUT                 : Évaluer un joueur (id_j, note, commentaire).            ParticipationDAO::evaluer_joueur()

> Les vérifications de cette api, conformément au projet :
>     - Entre 5 et 7 titulaires requis. Maximum 7 remplaçants.
>     - Seuls les joueurs n'ayant pas le statut "Supprimé" peuvent être ajoutés.
>     - Impossible de modifier la feuille d'un match déjà passé.


### API Statistiques (`apiGestionStats.php`)
Regroupe tous les GET de statistiques pour le dashboard.

- GET + ?type=globales : Statistiques globales (matchs, victoires...).          MatchDAO::obtenir_stats_globales()
- GET + ?type=serie    : Série de résultats en cours.                           MatchDAO::obtenir_serie_en_cours()
- GET + ?type=top      : Top des joueurs (participations).                      ParticipationDAO::obtenir_top_participations()
- GET + ?type=joueurs  : Stats détaillées par joueur (titularisations...).      ParticipationDAO::obtenir_stats_joueurs()


## Adresses des API
Les adresses dépendent de l'installation (voir le README racine) ; en local : `http://127.0.0.1:8001/authapi.php` (authentification) et `http://127.0.0.1:8002/apiGestion*.php` (gestion).
