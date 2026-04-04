<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT * FROM Prestataire ORDER BY nom_prestataire";
$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);
$prestataires = [];
while ($row = oci_fetch_assoc($stmt)) {
    $prestataires[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prestataires</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Registre des Prestataires</h1>

    <a href="ajouter.php" style="display:inline-block; margin-bottom:15px; padding:8px 12px; background:#4CAF50; color:white; text-decoration:none;">+ Ajouter un prestataire</a>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom Entreprise / Contact</th>
                <th>Spécialité (Secteur)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($prestataires as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['ID_PRESTATAIRE']); ?></td>
                    <td><strong><?php echo htmlspecialchars($p['NOM_PRESTATAIRE']); ?></strong></td>
                    <td><?php echo htmlspecialchars($p['SPECIALITE_PRESTATAIRE']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($prestataires)): ?>
                <tr><td colspan="3">Aucun prestataire partenaire enregistré.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>