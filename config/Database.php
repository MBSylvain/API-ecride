<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function connect() {
        $this->conn = null;

        // Récupérer les variables d'environnement

    $this->host = getenv('DB_HOST') ?: 'localhost';
    $this->db_name = getenv('DB_NAME') ?: 'covoiturage_db';
    $this->username = getenv('DB_USER') ?: 'root';
    $this->password = getenv('DB_PASSWORD') ?: '';
    $port = getenv('DB_PORT') ?: '3306';


        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';port=' . $port . ';dbname=' . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec('set names utf8');
            //echo 'Connexion réussie à la base de données.';
        } catch(PDOException $e) {
            echo 'Erreur de connexion : ' . $e->getMessage();
        }

        return $this->conn;
    }
}
?>