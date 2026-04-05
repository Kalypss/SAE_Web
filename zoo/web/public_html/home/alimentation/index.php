<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT al.ID_ALIMENTATION, al.DOSE_JOURNALIERE_ALIMENTATION, 
                 TO_CHAR(al.DATE_ALIMENTATION, 'DD/MM/YYYY') as DATE_ALIMENTATION, 
                 a.NOM_ANIMAL, p.NOM_PERSONNEL, p.PRENOM_PERSONNEL
          FROM Alimentation al
          JOIN Animaux a ON al.id_animaux = a.id_animaux
          JOIN Personnel p ON al.id_personnel = p.id_personnel
          ORDER BY al.date_alimentation DESC FETCH FIRST 50 ROWS ONLY";

$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);

$alimentations = [];
while ($row = oci_fetch_assoc($stmt)) {
    $alimentations[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleindex.css">
    <title>Gestion de l'Alimentation</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Registre des Alimentations</h1>

    <a href="ajouter.php">+ Ajouter une répartition alimentaire</a>
    <br>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Animal Cible</th>
                <th>Dose (kg/units)</th>
                <th>Date</th>
                <th>Soigneur/Personnel responsable</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alimentations as $al): ?>
                <tr>
                    <td><?php echo htmlspecialchars($al['ID_ALIMENTATION']); ?></td>
                    <td><?php echo htmlspecialchars($al['NOM_ANIMAL']); ?></td>
                    <td><?php echo htmlspecialchars($al['DOSE_JOURNALIERE_ALIMENTATION']); ?></td>
                    <td><?php echo htmlspecialchars($al['DATE_ALIMENTATION']); ?></td>
                    <td><?php echo htmlspecialchars($al['PRENOM_PERSONNEL'] . ' ' . $al['NOM_PERSONNEL']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($alimentations)): ?>
                <tr><td colspan="5">Aucun historique d'alimentation.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>