<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT s.id_soin, TO_CHAR(s.date_soin, 'DD/MM/YYYY') as date_soin, s.type_soin, s.description_soin, a.id_animaux, a.nom_animal, p.prenom_personnel, p.nom_personnel 
          FROM Soins s
          JOIN Animaux a ON s.id_animaux = a.id_animaux
          JOIN Personnel p ON s.id_personnel = p.id_personnel
          ORDER BY s.date_soin DESC";
$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);
$soins = [];
while ($row = oci_fetch_assoc($stmt)) {
    $soins[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleindex.css">
    <title>Soins</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Registre des Soins</h1>

    <a href="ajouter.php">+ Enregistrer un soin</a>
    <br>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>ID Soin</th>
                <th>Date</th>
                <th>Type</th>
                <th>Animal Patient</th>
                <th>Responsable (Vétérinaire/Soignant)</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($soins as $s): ?>
                <tr>
                    <td><?php echo htmlspecialchars($s['ID_SOIN']); ?></td>
                    <td><?php echo htmlspecialchars($s['DATE_SOIN']); ?></td>
                    <td>
                        <a href="view.php?id=<?php echo urlencode($s['ID_SOIN']); ?>">
                            <strong><?php echo htmlspecialchars($s['TYPE_SOIN']); ?></strong>
                        </a>
                    </td>
                    <td>
                        <a href="../animaux/view.php?id=<?php echo urlencode($s['ID_ANIMAUX']); ?>">
                            <?php echo htmlspecialchars($s['NOM_ANIMAL']); ?> (ID #<?php echo htmlspecialchars($s['ID_ANIMAUX']); ?>)
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($s['PRENOM_PERSONNEL'].' '.$s['NOM_PERSONNEL']); ?></td>
                    <td><?php echo htmlspecialchars($s['DESCRIPTION_SOIN']); ?></td>
                    <td>
                        <a href="view.php?id=<?php echo urlencode($s['ID_SOIN']); ?>">Détails</a> |
                        <a href="modifier.php?id=<?php echo urlencode($s['ID_SOIN']); ?>">Modifier</a> |
                        <a href="supprimer.php?id=<?php echo urlencode($s['ID_SOIN']); ?>" style="color:red;">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>