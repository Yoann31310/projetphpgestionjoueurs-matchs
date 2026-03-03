<?php
require_once __DIR__ . '/../DAO/JoueurDAO.php';

class Joueur {
	private $id_joueurs;
	private $numero_licence;
	private $nom;
	private $prenom;
	private $date_naissance;
	private $taille;
	private $poids;
	private $statut;

	// ========== GETTERS ==========
	
	public function get_id_joueurs() {  		    return $this->id_joueurs; }
	public function get_numero_licence() {		    return $this->numero_licence; }
	public function get_nom() {         		    return $this->nom;}
	public function get_prenom() {      		    return $this->prenom;}
	public function get_date_naissance() {		    return $this->date_naissance;}
	public function get_taille() {      		    return $this->taille;}
	public function get_poids() {       		    return $this->poids;}
	public function get_statut() {      		    return $this->statut;}

	// Vérifier si le joueur est actif (non supprimé)
	public function est_actif() {       		    return $this->statut !== 'Supprimé';}

	// Obtenir le nom complet du joueur (NOM en majuscules + prénom)
	public function get_nom_complet() { 		    return strtoupper($this->nom) . " " . $this->prenom;}


	public function set_id_joueurs($id) {	    	$this->id_joueurs = $id;}
	public function set_numero_licence($num) {		$this->numero_licence = $num;}
	public function set_nom($nom) {         		$this->nom = $nom;}
	public function set_prenom($prenom) {   		$this->prenom = $prenom;}
	public function set_date_naissance($date) {		$this->date_naissance = $date;}
	public function set_taille($taille) {   		$this->taille = $taille;}
	public function set_poids($poids) {     		$this->poids = $poids;}
	public function set_statut($statut) {           $this->statut = $statut;}


	// Calculer l'âge du joueur à partir de sa date de naissance
	public function calculer_age() {
		if ($this->date_naissance) {
			$date_nais = new DateTime($this->date_naissance);
			$maintenant = new DateTime();
			// Différence en années entre les deux dates
			return $maintenant->diff($date_nais)->y;
		}
		return null;
	}

	// Récupérer uniquement les joueurs actifs
	public static function recuperer_actifs() {		        return JoueurDAO::recuperer_actifs();}

	// Récupérer tous les joueurs (même supprimés)
	public static function recuperer_tout() {		        return JoueurDAO::recuperer_tout();}

	// Trouver un joueur par son identifiant
	public static function trouver_par_id($id) {	        return JoueurDAO::trouver_par_id($id);}

	// Trouver un joueur par son numéro de licence
	public static function trouver_par_licence($licence) {
		return JoueurDAO::trouver_par_licence($licence);
	}

	// Trouver un joueur par nom et prénom
	public static function trouver_par_nom_prenom($nom, $prenom, $id_exclusion = 0) {
		return JoueurDAO::trouver_par_nom_prenom($nom, $prenom, $id_exclusion);
	}

	// Vérifier si une licence existe déjà
	public static function licence_existe_deja($licence, $id_exclusion = 0) {
		return JoueurDAO::licence_existe_deja($licence, $id_exclusion);
	}

	// Ajouter un nouveau joueur
	public static function ajouter(Joueur $joueur) {		return JoueurDAO::ajouter($joueur);	}

	// Modifier un joueur existant
	public static function modifier($id, Joueur $joueur) {	return JoueurDAO::modifier($id, $joueur);}

	// Supprimer un joueur (soft delete : changement de statut)
	public static function supprimer($id) {         		return JoueurDAO::supprimer($id);}
}