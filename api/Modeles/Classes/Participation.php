<?php
require_once __DIR__ . '/../DAO/ParticipationDAO.php';

class Participation {
    public $Id_Joueurs;
    public $Id_Matchs;
    public $feuille_match;
    public $evaluation;
    public $nom_poste;
    public $est_Capitaine;
    public $commentaire;

    public function get_id_joueurs()    { return $this->Id_Joueurs; }
    public function get_id_matchs()     { return $this->Id_Matchs; }
    public function get_feuille_match() { return $this->feuille_match; }
    public function get_evaluation()    { return $this->evaluation; }
    public function get_nom_poste()     { return $this->nom_poste; }
    public function get_est_capitaine() { return $this->est_Capitaine; }
    public function get_commentaire()   { return $this->commentaire; }

    public function set_id_joueurs($valeur)    { $this->Id_Joueurs = $valeur; }
    public function set_id_matchs($valeur)     { $this->Id_Matchs = $valeur; }
    public function set_feuille_match($valeur) { $this->feuille_match = $valeur; }
    public function set_evaluation($valeur)    { $this->evaluation = $valeur; }
    public function set_nom_poste($valeur)     { $this->nom_poste = $valeur; }
    public function set_est_capitaine($valeur) { $this->est_Capitaine = $valeur; }
    public function set_commentaire($valeur)   { $this->commentaire = $valeur; }

    public function est_titulaire()  { return strtolower($this->feuille_match) === 'titulaire'; }
    public function est_remplacant() { return strtolower($this->feuille_match) === 'remplaçant'; }

    public static function recuperer_participants($id_match)                      { return ParticipationDAO::recuperer_participants($id_match); }
    public static function ajouter_participant($id_match, $id_joueur, $role, $poste) { return ParticipationDAO::ajouter_participant($id_match, $id_joueur, $role, $poste); }
    public static function modifier_participant($id_match, $id_joueur, $role, $poste) { return ParticipationDAO::modifier_participant($id_match, $id_joueur, $role, $poste); }
    public static function vider_feuille($id_match)                               { return ParticipationDAO::vider_feuille($id_match); }
    public static function retirer_participant($id_match, $id_joueur)             { return ParticipationDAO::retirer_participant($id_match, $id_joueur); }
    public static function evaluer_joueur($id_match, $id_joueur, $note, $comm)    { return ParticipationDAO::evaluer_joueur($id_match, $id_joueur, $note, $comm); }
}
