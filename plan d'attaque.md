# Plan d'Attaque
## État d'Avancement : Terminé

- `apiGestion.php` : Le refactoring pour envoyer les réponses et pour décoder les jsons, et aussi vérifier que l'entraineur est connecté (en faisant des appels à l'api de connexion)

### API Joueurs (`apiGestionJoueur.php`)
- GET           : Récupération des joueurs actifs.              JoueurDAO::recuperer_actifs()
- GET + id      : Récupération d'un joueur par son ID.          JoueurDAO::trouver_par_id($id)
- POST          : Création d'un nouveau joueur.                 JoueurDAO::ajouter($joueur)
- PUT + id      : Mise à jour complète du joueur.               JoueurDAO::modifier($id, $joueur)
- DELETE + id   : Suppression d'un joueur.                      JoueurDAO::supprimer($id)

### API Matchs (`apiGestionMatch.php`)
- GET           : Récupération de tous les matchs.              MatchDAO::recuperer_tout()
- GET + id      : Récupération d'un match par son ID.           MatchDAO::trouver_par_id($id)
- POST          : Programmation d'un nouveau match.             MatchDAO::ajouter($match)
- PUT + id      : Mise à jour d'un match (et du résultat).      MatchDAO::modifier($id, $match)
- DELETE + id   : Suppression d'un match.                       MatchDAO::supprimer($id)

---

## A faire 

### API Feuilles de Match (`apiGestionFeuilleMatch.php`)
Utilise les méthodes de `ParticipationDAO`

- GET + id_match      : Lister les participants d'un match.                     recuperer_participants()
- POST                : Ajouter un participant (id_j, role, poste).             ajouter_participant()
- DELETE + id_match   : Vider la feuille de match.                              vider_feuille()
- PUT                 : Évaluer un joueur (id_j, note, commentaire).            evaluer_joueur()

### API Statistiques (`apiGestionStats.php`)
Regroupe tous les GET de statistiques pour le dashboard.

- GET + ?type=globales : Statistiques globales (matchs, victoires...).          MatchDAO::obtenir_stats_globales()
- GET + ?type=serie    : Série de résultats en cours.                           MatchDAO::obtenir_serie_en_cours()
- GET + ?type=top      : Top des joueurs (participations).                      ParticipationDAO::obtenir_top_participations()
- GET + ?type=joueurs  : Stats détaillées par joueur (titularisations...).      ParticipationDAO::obtenir_stats_joueurs()

---

## Nettoyage & Optimisations
- Vérifier la cohérence des codes de retour des requêtes qu'on fait
- Faire une documentation des API
- Vérification systématique du JWT sur chaque point d'entrée via `apiGestion.php`.
- Tout mettre dans un .env() même si bon... Techniquement c trop tard...

## Notes
- La vérif de jwt se fait dans toutes les api (y compris dans ./projetphpgestionjoueurs-matchs, tout "l'impotant" se fait dans apiGestion, car elle redirige tout)
