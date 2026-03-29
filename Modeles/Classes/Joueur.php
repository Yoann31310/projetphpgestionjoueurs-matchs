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

    public function get_id_joueurs()      { return $this->Id_Joueurs; }
    public function get_numero_licence()  { return $this->Numero_licence; }
    public function get_nom()             { return $this->nom; }
    public function get_prenom()          { return $this->prenom; }
    public function get_date_naissance()  { return $this->date_naissance; }
    public function get_taille()          { return $this->taille; }
    public function get_poids()           { return $this->poids; }
    public function get_statut()          { return $this->statut; }

    public function set_id_joueurs($valeur)      { $this->Id_Joueurs = $valeur; }
    public function set_numero_licence($valeur)  { $this->Numero_licence = $valeur; }
    public function set_nom($valeur)             { $this->nom = $valeur; }
    public function set_prenom($valeur)          { $this->prenom = $valeur; }
    public function set_date_naissance($valeur)  { $this->date_naissance = $valeur; }
    public function set_taille($valeur)          { $this->taille = $valeur; }
    public function set_poids($valeur)           { $this->poids = $valeur; }
    public function set_statut($valeur)          { $this->statut = $valeur; }

    public static function recuperer_actifs()                                    { return JoueurDAO::recuperer_actifs(); }
    public static function trouver_par_id($id)                                   { return JoueurDAO::trouver_par_id($id); }
    public static function ajouter(Joueur $joueur)                               { return JoueurDAO::ajouter($joueur); }
    public static function modifier($id, Joueur $joueur)                         { return JoueurDAO::modifier($id, $joueur); }
    public static function supprimer($id)                                        { return JoueurDAO::supprimer($id); }
    public static function ajouter_commentaire($id_joueur, $commentaire)         { return JoueurDAO::ajouter_commentaire($id_joueur, $commentaire); }
    public static function licence_existe_deja($licence, $id_exclusion = 0)      { return JoueurDAO::licence_existe_deja($licence, $id_exclusion); }
    public static function trouver_par_nom_prenom($nom, $prenom, $id_exclu = 0)  { return JoueurDAO::trouver_par_nom_prenom($nom, $prenom, $id_exclu); }
    public static function a_participe($id)                                      { return JoueurDAO::a_participe($id); }
}
