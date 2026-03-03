<?php
require_once __DIR__ . '/../DAO/MatchDAO.php';

class Matchs {
	private $id_matchs;
	private $date_heure;
	private $nom_equipe_adverse;
	private $lieu;
	private $adresse;
	private $resultat;

	


    public function get_id_matchs() {               return $this->id_matchs; }
    public function get_date_heure() {              return $this->date_heure; }
    public function get_nom_equipe_adverse() {      return $this->nom_equipe_adverse; }
    public function get_lieu() {                    return $this->lieu; }
    public function get_adresse() {                 return $this->adresse; }
    public function get_resultat() {                return $this->resultat; }

    public function set_id_matchs($id) {            $this->id_matchs = $id; }
    public function set_date_heure($date) {         $this->date_heure = $date; }
    public function set_nom_equipe_adverse($nom) {  $this->nom_equipe_adverse = $nom; }
    public function set_lieu($lieu) {               $this->lieu = $lieu; }
    public function set_adresse($adresse) {         $this->adresse = $adresse; }
    public function set_resultat($resultat) {       $this->resultat = $resultat; }

    	// Vérifier si le match est déjà passé
	public function est_passe() {
		// Convertit la date en timestamp et compare avec maintenant
		return strtotime($this->date_heure) < time();
	}

	public function est_a_venir() {
        // Convertis en time
		return strtotime($this->date_heure) >= time();
    }

    public function est_termine() {                 
        return $this->resultat !== null; 
    }
    
    // Vérifier si le match a été gagné
	public function est_victoire() {
		// Convertit le résultat en minuscules pour la comparaison
		return strtolower($this->resultat) === 'gagnée';
	}
    
    // Récupérer tous les matchs
    public static function recuperer_tout() {               return MatchDAO::recuperer_tout();}

    public static function trouver_par_id($id) {            return MatchDAO::trouver_par_id($id);}

    public static function ajouter(Matchs $match) {         return MatchDAO::ajouter($match);}

    public static function modifier($id, Matchs $match) {   return MatchDAO::modifier($id, $match); }

    public static function supprimer($id) {                 return MatchDAO::supprimer($id);}
}