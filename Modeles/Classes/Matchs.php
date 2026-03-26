<?php
require_once __DIR__ . '/../DAO/MatchDAO.php';

class Matchs {
	public $Id_Matchs;
	public $Date_heure;
	public $nom_equipe_adverse;
	public $lieu;
	public $adresse;
	public $resultat;

    public function get_id_matchs() {               return $this->Id_Matchs; }
    public function get_date_heure() {              return $this->Date_heure; }
    public function get_nom_equipe_adverse() {      return $this->nom_equipe_adverse; }
    public function get_lieu() {                    return $this->lieu; }
    public function get_adresse() {                 return $this->adresse; }
    public function get_resultat() {                return $this->resultat; }

    public function set_id_matchs($id) {            $this->Id_Matchs = $id; }
    public function set_date_heure($date) {         $this->Date_heure = $date; }
    public function set_nom_equipe_adverse($nom) {  $this->nom_equipe_adverse = $nom; }
    public function set_lieu($lieu) {               $this->lieu = $lieu; }
    public function set_adresse($adresse) {         $this->adresse = $adresse; }
    public function set_resultat($resultat) {       $this->resultat = $resultat; }

    // Vérifier si le match est déjà passé
	public function est_passe() {
		return strtotime($this->Date_heure) < time();
	}

    public static function recuperer_tout() {
        return MatchDAO::recuperer_tout();
    }

    public static function trouver_par_id($id) {
        return MatchDAO::trouver_par_id($id);
    }

    public static function ajouter(Matchs $match) {
        return MatchDAO::ajouter($match);
    }

    public static function modifier($id, Matchs $match) {
        return MatchDAO::modifier($id, $match);
    }

    public static function supprimer($id) {
        return MatchDAO::supprimer($id);
    }
}
