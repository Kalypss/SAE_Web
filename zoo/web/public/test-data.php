<?php
/**
 * Fichier de test des données stockées dans une base Oracle.
 *
 * @author Florian SILVA
 * @version 0.1
 * @date 2026-04-01
 */
require_once '../config/database.php'; // Inclure le fichier de connexion

// Récupérer la liste des tables
$tables = [];
try {
    $stmt = oci_parse($conn, "SELECT table_name FROM user_tables");
    oci_execute($stmt);
    while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
        $tables[] = $row['TABLE_NAME'];
    }
} catch (Exception $e) {
    echo "Erreur lors de la récupération des tables : {$e->getMessage()}";
    exit(1);
}

// Vérifier que des données existent dans une table
if (!empty($tables)) {
    $dataExists = false;
    foreach ($tables as $table) {
        try {
            $stmt = oci_parse($conn, "SELECT COUNT(*) FROM $table");
            oci_execute($stmt);
            $count = oci_fetch_array($stmt, OCI_NUM)[0];
            if ($count > 0) {
                echo "Données trouvées dans la table $table !";
                $dataExists = true;
                break;
            }
        } catch (Exception $e) {
            echo "Erreur lors du test des données dans $table : {$e->getMessage()}";
        }
    }
    if (!$dataExists) {
        echo "Aucune donnée trouvée dans les tables testées.";
    }
} else {
    echo "Aucune table trouvée dans la base Oracle.";
}
?>