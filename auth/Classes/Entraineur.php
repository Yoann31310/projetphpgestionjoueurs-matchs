<?php
require_once __DIR__ . '/../DAO/EntraineurDAO.php';

class Entraineur
{
    private $id_entraineur;
    private $identifiant;
    private $mdp;
    private $nom;
    private $prenom;
    private $email;

    public function get_id_entraineur() { return $this->id_entraineur; }
    public function get_identifiant() { return $this->identifiant; }
    public function get_mdp() { return $this->mdp; }
    public function get_nom() { return $this->nom; }
    public function get_prenom() { return $this->prenom; }
    public function get_email() { return $this->email; }
    public function get_nom_complet() { return $this->prenom . " " . strtoupper($this->nom); }

    public function set_id_entraineur($id) { $this->id_entraineur = $id; }
    public function set_identifiant($ident) { $this->identifiant = $ident; }
    public function set_mdp($mdp) { $this->mdp = $mdp; }
    public function set_nom($nom) { $this->nom = $nom; }
    public function set_prenom($prenom) { $this->prenom = $prenom; }
    public function set_email($email) { $this->email = $email; }


    public function verifier_mot_de_passe($mdp_saisi) { return password_verify($mdp_saisi, $this->mdp); }

    // Return un objet Entraineur ou false
    public static function verifier_identifiant($identifiant) { return EntraineurDAO::verifier_identifiant($identifiant); }

    // Trouver par ID
    public static function trouver_par_id($id) { return EntraineurDAO::trouver_par_id($id); }
}
