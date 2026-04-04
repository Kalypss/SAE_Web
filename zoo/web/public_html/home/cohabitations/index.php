<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT c.id_espece, e1.nomU_espece AS esp1_nom, 
                 c.id_espece_1, e2.nomU_espece AS esp2_nom 
          FROM Cohabitation c
          JOIN Especes e1 ON c.id_espece = e1.id_espece
          JOIN Especes e2 ON c.id_espece_1 = e2.id_espece";
$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);
$cohabitations = [];
while ($row = oci_fetch_assoc($stmt)) {
    $cohabitations[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cohabitations</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Cohabitations Possibles entre Espèces</h1>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Espèce 1</th>
                <th>Espèce 2</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($cohabitations as $c): ?>
                <tr>
                    <td>
                        <a href="../especes/view.php?id=<?php echo urlencode($c['ID_ESPECE']); ?>">
                            <?php echo htmlspecialchars($c['ESP1_NOM']); ?>
                        </a>
                    </td>
                    <td>
                        <a href="../especes/view.php?id=<?php echo urlencode($c['ID_ESPECE_1']); ?>">
                            <?php echo htmlspecialchars($c['ESP2_NOM']); ?>
                        </a>
                    </td>
                    <td>
                        <!-- TODO: Supprimer cohabitation (Optionnel) -->
                        <span style="color:grey;">Cohabitation certifiée</span>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($cohabitations)): ?>
                <tr><td colspan="3">Aucune règle de cohabitation définie.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>