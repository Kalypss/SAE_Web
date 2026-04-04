<?php
/**
 * Fichier de connexion à une base de données Oracle.
 * Utilise des constantes de configuration.
 */

// Importation des paramètres de connexion
require_once __DIR__ . '/myparam.inc.php';

// Connexion à la base de données Oracle
try {
    $conn = @oci_connect(
        MYUSER,
        MYPASS,
        MYHOST,
        'AL32UTF8'
    );
    if (!$conn) {
        $e = oci_error();
        throw new Exception('Erreur de connexion à la base de données.'); // On ne dévoile pas le message d'erreur d'Oracle en prod
    }
} catch (Exception $e) {
    // Log l'erreur réelle en interne si nécessaire (error_log($e->getMessage()))
    die('Service indisponible. Veuillez réessayer plus tard.');
}
?>
