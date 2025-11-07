<?php
try {
    $pdo = new PDO(
        'mysql:host=interchange.proxy.rlwy.net;port=57160;dbname=railway',
        'root',
        'xegHLqIRdqENmjgkdgnpQHLCVccvCEmu'
    );
    echo "Connexion réussie !";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>