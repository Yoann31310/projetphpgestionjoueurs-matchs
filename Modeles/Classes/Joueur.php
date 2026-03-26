<?php
require_once __DIR__ . '/../DAO/JoueurDAO.php';

class Joueur {
	public $Id_Joueurs;
	public $Numero_licence;
	public $nom;
	public $prenom;
	public $date_naissance;
	public $taille;
	public $poids;
	public $statut;

	// ========== GETTERS ==========
	public function get_id_joueurs() {  		    return $this->Id_Joueurs; }
	public function get_numero_licence() {		    return $this->Numero_licence; }
	public function get_nom() {         		    return $this->nom;}
	public function get_prenom() {      		    return $this->prenom;}
	public function get_date_naissance() {		    return $this->date_naissance;}
	public function get_taille() {      		    return $this->taille;}
	public function get_poids() {       		    return $this->poids;}
	public function get_statut() {      		    return $this->statut;}

	public function set_id_joueurs($id) {	    	$this->Id_Joueurs = $id;}
	public function set_numero_licence($num) {		$this->Numero_licence = $num;}
	public function set_nom($nom) {         		$this->nom = $nom;}
	public function set_prenom($prenom) {   		$this->prenom = $prenom;}
	public function set_date_naissance($date) {		$this->date_naissance = $date;}
	public function set_taille($taille) {   		$this->taille = $taille;}
	public function set_poids($poids) {     		$this->poids = $poids;}
	public function set_statut($statut) {           $this->statut = $statut;}

    public static function recuperer_actifs() {
        return JoueurDAO::recuperer_actifs();
    }

    public static function trouver_par_id($id) {
        return JoueurDAO::trouver_par_id($id);
    }

    public static function ajouter(Joueur $joueur) {
        return JoueurDAO::ajouter($joueur);
    }

    public static function modifier($id, Joueur $joueur) {
        return JoueurDAO::modifier($id, $joueur);
    }

    public static function supprimer($id) {
        return JoueurDAO::supprimer($id);
    }

    public static function ajouter_commentaire($id_joueur, $commentaire) {
        return JoueurDAO::ajouter_commentaire($id_joueur, $commentaire);
    }
}
