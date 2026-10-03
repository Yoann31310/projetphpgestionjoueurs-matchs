<?php
// Connexion à la base de données du service de gestion (classe Singleton).
// Les identifiants viennent de la configuration (voir config.php et .env.example).
require_once __DIR__ . '/../config.php';

class Database
{
    private static $instance = null;
    private $connexion;

    private function __construct()
    {
        $hote = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $base = env('DB_NAME');
        $utilisateur = env('DB_USER');
        $mot_de_passe = env('DB_PASSWORD', '');

        if ($base === null || $utilisateur === null) {
            throw new RuntimeException('Configuration de la base de données incomplète (DB_NAME, DB_USER).');
        }

        try {
            $this->connexion = new PDO(
                "mysql:host=$hote;port=$port;dbname=$base;charset=utf8mb4",
                $utilisateur,
                $mot_de_passe,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            // Le détail technique reste dans les journaux du serveur, jamais dans la réponse au client
            error_log('[api] connexion BD impossible : ' . $e->getMessage());
            throw new RuntimeException('Base de données indisponible.');
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->connexion;
    }
}
