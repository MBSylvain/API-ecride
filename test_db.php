<?php
// Test de connexion à la base de données Railway
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'covoiturage_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$port = getenv('DB_PORT') ?: '3306';

echo "Test de connexion à la base de données...<br>";
echo "Host: $host<br>DB: $db<br>User: $user<br>Port: $port<br>";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('set names utf8');
    echo '<span style="color:green">Connexion réussie à la base de données Railway !</span>';
} catch (PDOException $e) {
    echo '<span style="color:red">Erreur de connexion : ' . $e->getMessage() . '</span>';
}
?>