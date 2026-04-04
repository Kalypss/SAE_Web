<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT e.id_enclo, e.surface_enclo, e.latitude_enclo, e.longitude_enclo, z.nom_zone 
          FROM Enclos e 
          JOIN Zone z ON e.id_zone = z.id_zone
          ORDER BY e.id_enclo";
$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);
$enclos = [];
while ($row = oci_fetch_assoc($stmt)) {
    $enclos[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enclos</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Infrastructures - Liste des Enclos</h1>

    <a href="ajouter.php" style="display:inline-block; margin-bottom:15px; padding:8px 12px; background:#4CAF50; color:white; text-decoration:none;">+ Créer un enclos</a>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Enclos N°</th>
                <th>Zone Thématique</th>
                <th>Surface (m²)</th>
                <th>Coordonnées</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($enclos as $e): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($e['ID_ENCLO']); ?></strong></td>
                    <td><?php echo htmlspecialchars($e['NOM_ZONE']); ?></td>
                    <td><?php echo htmlspecialchars($e['SURFACE_ENCLO']); ?> m²</td>
                    <td><?php echo htmlspecialchars($e['LATITUDE_ENCLO'] . ', ' . $e['LONGITUDE_ENCLO']); ?></td>
                    <td>
                        <a href="view.php?id=<?php echo urlencode($e['ID_ENCLO']); ?>">Fiche Enclos</a>
                        | <a href="modifier.php?id=<?php echo urlencode($e['ID_ENCLO']); ?>">Modifier</a>
                        <!-- TODO: Delete if needed later -->
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($enclos)): ?>
                <tr><td colspan="5">Aucun enclos défini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>