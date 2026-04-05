<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT * FROM Especes ORDER BY nomU_espece";
$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);
$especes = [];
while ($row = oci_fetch_assoc($stmt)) {
    $especes[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleindex.css">
    <title>Liste des Espèces</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Liste des Espèces</h1>

    <a href="ajouter.php" >+ Ajouter une espèce</a>
    <br>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom Usuel</th>
                <th>Nom Latin</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($especes as $esp): ?>
                <tr>
                    <td><?php echo htmlspecialchars($esp['ID_ESPECE']); ?></td>
                    <td><?php echo htmlspecialchars($esp['NOMU_ESPECE']); ?></td>
                    <td><i><?php echo htmlspecialchars($esp['NOML_ESPECE']); ?></i></td>
                    <td>
                        <?php echo ($esp['EST_MENACE'] == 1) ? '<span style="color: white; background-color: red; padding: 2px 5px; border-radius: 3px; font-size: 0.8em; font-weight: bold;">Menacée</span>' : 'Classique'; ?>
                    </td>
                    <td>
                        <a href="view.php?id=<?php echo urlencode($esp['ID_ESPECE']); ?>">Fiche</a>
                        | <a href="modifier.php?id=<?php echo urlencode($esp['ID_ESPECE']); ?>">Modifier</a>
                        | <a href="supprimer.php?id=<?php echo urlencode($esp['ID_ESPECE']); ?>" style="color:red;">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($especes)): ?>
                <tr><td colspan="5">Aucune espèce.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>