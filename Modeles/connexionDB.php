<?php
// Pour se connecter à la base de données

class Database
{
    private static $instance = null;
    private $connexion;

    // Constructeur privé car classe Singleton
    private function __construct()
    {
        try {
            $host = 'mysql-alphonse.alwaysdata.net';
            $bd = 'alphonse_bd_api_gestion';
            $utilisateur = 'alphonse';
            $mdp = 'azertyuiop.@';

            $this->connexion = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $utilisateur, $mdp);

            // On force PDO à afficher les erreurs SQL
            $this->connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    // Servira pour après, pour accéder à l'instance de la BD existante
    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new Database();
        return self::$instance->connexion;
    }
}