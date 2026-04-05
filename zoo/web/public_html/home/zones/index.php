<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT id_zone, nom_zone FROM Zone ORDER BY nom_zone ASC";
$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);

$zones = [];
while ($row = oci_fetch_assoc($stmt)) {
    $zones[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleindex.css">
    <title>Secteurs et Zones</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Secteurs Géographiques du Parc (Zones)</h1>

    <a href="ajouter.php">+ Créer une nouvelle Zone</a>
    <br>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID Zone</th>
                <th>Nom du Secteur (Zone)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($zones as $z): ?>
                <tr>
                    <td><?php echo htmlspecialchars($z['ID_ZONE']); ?></td>
                    <td><?php echo htmlspecialchars($z['NOM_ZONE']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($zones)): ?>
                <tr><td colspan="2">Aucune zone configurée dans le zoo.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>