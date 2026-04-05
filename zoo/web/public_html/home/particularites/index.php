<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT id_particularite, nom_particularite FROM Particularite ORDER BY nom_particularite ASC";
$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);

$particularites = [];
while ($row = oci_fetch_assoc($stmt)) {
    $particularites[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleindex.css">
    <title>Référentiel des Particularités</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Dictionnaire des Particularités Animales</h1>

    <a href="ajouter.php">+ Ajouter un attribut de spécificité</a>
    <br>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID Particularité</th>
                <th>Intitulé descriptif (Caractéristique)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($particularites as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['ID_PARTICULARITE']); ?></td>
                    <td><?php echo htmlspecialchars($p['NOM_PARTICULARITE']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($particularites)): ?>
                <tr><td colspan="2">Aucune particularité biologique/spécifique trouvée en base.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>