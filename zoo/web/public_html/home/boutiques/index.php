<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT b.id_boutique, b.type_boutique, z.nom_zone
          FROM Boutique b
          JOIN Zone z ON b.id_zone = z.id_zone
          ORDER BY b.id_boutique DESC";
$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);
$boutiques = [];
while ($row = oci_fetch_assoc($stmt)) { $boutiques[] = $row; }
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleindex.css">
    <title>Gestion des Boutiques</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Boutiques du Parc</h1>

    <a href="ajouter.php">+ Référencer une nouvelle boutique</a>
    <br>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID Boutique</th>
                <th>Type de Boutique</th>
                <th>Zone Localisée</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($boutiques as $b): ?>
                <tr>
                    <td><?php echo htmlspecialchars($b['ID_BOUTIQUE']); ?></td>
                    <td><?php echo htmlspecialchars($b['TYPE_BOUTIQUE']); ?></td>
                    <td><?php echo htmlspecialchars($b['NOM_ZONE']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($boutiques)): ?>
                <tr><td colspan="3">Aucune boutique définie.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>