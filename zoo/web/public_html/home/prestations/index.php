<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT id_prestation, description_prestation, niveau_requis 
          FROM Prestation 
          ORDER BY id_prestation ASC";

$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);

$prestations = [];
while ($row = oci_fetch_assoc($stmt)) {
    $prestations[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catalogue des Prestations</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Prestations / Activités Complémentaires</h1>

    <a href="ajouter.php" style="display:inline-block; padding:10px; background:green; color:white; text-decoration:none; margin-bottom:15px;">+ Créer une Prestation</a>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Description de l'Activité</th>
                <th>Niveau Requis (Avantage Parrainage)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prestations as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['ID_PRESTATION']); ?></td>
                    <td><?php echo htmlspecialchars($p['DESCRIPTION_PRESTATION']); ?></td>
                    <td>
                        <strong style="color: <?php 
                            if($p['NIVEAU_REQUIS']=='or') echo 'gold';
                            elseif($p['NIVEAU_REQUIS']=='argent') echo 'gray';
                            elseif($p['NIVEAU_REQUIS']=='bronze') echo 'brown';
                            else echo 'black';
                        ?>">
                            <?php echo htmlspecialchars(ucfirst($p['NIVEAU_REQUIS'])); ?>
                        </strong>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($prestations)): ?>
                <tr><td colspan="3">Aucune prestation configurée.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>