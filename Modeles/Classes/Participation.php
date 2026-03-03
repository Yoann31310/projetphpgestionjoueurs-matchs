<?php
require_once __DIR__ . '/../DAO/ParticipationDAO.php';

class Participation {
	private $id_joueurs;
	private $id_matchs;
	private $feuille_match;
	private $evaluation;
	private $nom_poste;
	private $est_capitaine;
	private $commentaire;


    public function get_id_joueurs() {              return $this->id_joueurs; }
    public function get_id_matchs() {               return $this->id_matchs; }
    public function get_feuille_match() {           return $this->feuille_match; }
    public function get_evaluation() {              return $this->evaluation; }
    public function get_nom_poste() {               return $this->nom_poste; }
    public function get_est_capitaine() {           return $this->est_capitaine; }
    public function get_commentaire() {             return $this->commentaire; }

    public function set_id_joueurs($id) {           $this->id_joueurs = $id; }
    public function set_id_matchs($id) {            $this->id_matchs = $id; }
    public function set_feuille_match($role) {      $this->feuille_match = $role; }
    public function set_evaluation($note) {         $this->evaluation = $note; }
    public function set_nom_poste($poste) {         $this->nom_poste = $poste; }
    public function set_est_capitaine($est_cap) {   $this->est_capitaine = $est_cap; }
    public function set_commentaire($comm) {        $this->commentaire = $comm; }

    	// Vérifier si le joueur est titulaire pour ce match
	public function est_titulaire() {
		return strtolower($this->feuille_match) === 'titulaire';
	}

	// Vérifier si le joueur est remplaçant pour ce match
	public function est_remplacant() {
		return strtolower($this->feuille_match) === 'remplaçant';
	}


    // Récupérer les participants d'un match avec infos joueur
    public static function recuperer_participants($id_match) {
        return ParticipationDAO::recuperer_participants($id_match);
    }

    public static function ajouter_participant($id_match, $id_joueur, $role, $poste) {
        return ParticipationDAO::ajouter_participant($id_match, $id_joueur, $role, $poste);
    }

    // Vider la feuille de match
    public static function vider_feuille($id_match) {
        return ParticipationDAO::vider_feuille($id_match);
    }

    // Évaluer un joueur
    public static function evaluer_joueur($id_match, $id_joueur, $note, $commentaire) {
        return ParticipationDAO::evaluer_joueur($id_match, $id_joueur, $note, $commentaire);
    }
}