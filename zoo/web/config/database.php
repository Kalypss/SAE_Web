<?php
/**
 * Fichier de connexion à une base de données Oracle.
 * Utilise les identifiants stockés dans un fichier .env.
 *
 * @author Florian SILVA
 * @version 0.1
 * @date 2026-04-01
 */

require_once 'vendor/autoload.php'; // Charger l'autoloader si tu utilises Composer
use Dotenv\Dotenv; // Charger la librairie phpdotenv si tu l'installes

// Charger les variables d'environnement depuis .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Vérifier que les variables nécessaires sont présentes
if (!isset($_ENV['ORACLE_USER']) || !isset($_ENV['ORACLE_PASSWORD']) || !isset($_ENV['ORACLE_HOST']) || !isset($_ENV['ORACLE_PORT']) || !isset($_ENV['ORACLE_SERVICE_NAME'])) {
    die('Variables d environnement manquantes pour la connexion Oracle.');
}
// Connexion à la base de données Oracle
try {
    $conn = oci_connect(
        $_ENV['ORACLE_USER'],
        $_ENV['ORACLE_PASSWORD'],
        "//{$_ENV['ORACLE_HOST']}:{$_ENV['ORACLE_PORT']}/{$_ENV['ORACLE_SERVICE_NAME']}]"
    );
    if (!$conn) {
        $e = oci_error();
        throw new Exception("{$e['message']} (Code: {$e['code']})");
    }
    echo "Connexion Oracle réussie !";
} catch (Exception $e) {
    echo "Erreur de connexion : {$e->getMessage()}";
    exit(1);
}
?>