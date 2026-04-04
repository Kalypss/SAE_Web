<?php
/**
 * Fichier de test des données (adapté pour OCI8).
 */

// Inclure votre nouveau fichier myparam.inc.php qui contient MYHOST, MYUSER, MYPASS
require_once __DIR__ . '/../aaron/myparam.inc.php';

// Connexion via Oracle (OCI8)
$conn = oci_connect(MYUSER, MYPASS, MYHOST);

if (!$conn) {
    $e = oci_error();
    die("Erreur de connexion Oracle : " . htmlentities($e['message']));
}
echo "Connexion réussie avec Oracle OCI8 !<br>";

// Récupérer la liste des tables
$tables = [];
$stmt = oci_parse($conn, "SELECT table_name FROM user_tables");
oci_execute($stmt);
while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
    $tables[] = $row['TABLE_NAME'];
}

// Vérifier que des données existent dans une table
if (!empty($tables)) {
    $dataExists = false;
    foreach ($tables as $table) {
        $stmtCount = oci_parse($conn, "SELECT COUNT(*) FROM \"$table\"");
        if (oci_execute($stmtCount)) {
            $count = oci_fetch_array($stmtCount, OCI_NUM)[0];
            if ($count > 0) {
                echo "Données trouvées dans la table $table !<br>";
                $dataExists = true;
                break;
            }
        } else {
            $e = oci_error($stmtCount);
            echo "Erreur lors du test des données dans $table : " . htmlentities($e['message']) . "<br>";
        }
    }
    if (!$dataExists) {
        echo "Aucune donnée trouvée dans les tables testées.<br>";
    }
} else {
    echo "Aucune table trouvée dans la base.<br>";
}

oci_close($conn);
?>