<?php
require_once __DIR__ . '/../DAO/MatchDAO.php';

class Matchs {
    public $Id_Matchs;
    public $Date_heure;
    public $nom_equipe_adverse;
    public $lieu;
    public $adresse;
    public $resultat;

    public function get_id_matchs()          { return $this->Id_Matchs; }
    public function get_date_heure()         { return $this->Date_heure; }
    public function get_nom_equipe_adverse() { return $this->nom_equipe_adverse; }
    public function get_lieu()               { return $this->lieu; }
    public function get_adresse()            { return $this->adresse; }
    public function get_resultat()           { return $this->resultat; }

    public function set_id_matchs($valeur)          { $this->Id_Matchs = $valeur; }
    public function set_date_heure($valeur)         { $this->Date_heure = $valeur; }
    public function set_nom_equipe_adverse($valeur) { $this->nom_equipe_adverse = $valeur; }
    public function set_lieu($valeur)               { $this->lieu = $valeur; }
    public function set_adresse($valeur)            { $this->adresse = $valeur; }
    public function set_resultat($valeur)           { $this->resultat = $valeur; }

    public function est_passe() { return strtotime($this->Date_heure) < time(); }

    public static function recuperer_tout()              { return MatchDAO::recuperer_tout(); }
    public static function trouver_par_id($id)           { return MatchDAO::trouver_par_id($id); }
    public static function ajouter(Matchs $match)        { return MatchDAO::ajouter($match); }
    public static function modifier($id, Matchs $match)  { return MatchDAO::modifier($id, $match); }
    public static function supprimer($id)                { return MatchDAO::supprimer($id); }
}
