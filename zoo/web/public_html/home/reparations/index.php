<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT r.id_reparation, r.nature_reparation, TO_CHAR(r.date_reparation, 'DD/MM/YYYY') as date_rep,
                 p.nom_prestataire, e.id_enclo, z.nom_zone, pers.prenom_personnel, pers.nom_personnel
          FROM Reparation r
          JOIN Prestataire p ON r.id_prestataire = p.id_prestataire
          JOIN Enclos e ON r.id_enclo = e.id_enclo
          JOIN Zone z ON e.id_zone = z.id_zone
          JOIN Personnel pers ON r.id_personnel = pers.id_personnel
          ORDER BY r.date_reparation DESC";

$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);
$reparations = [];
while ($row = oci_fetch_assoc($stmt)) {
    $reparations[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleindex.css">
    <title>Réparations & Interventions</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Registre des Interventions Techniques</h1>

    <a href="ajouter.php">+ Déclarer une intervention</a>
    <br>
    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Enclos & Zone</th>
                <th>Prestataire</th>
                <th>Déclaré par</th>
                <th>Nature des travaux</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($reparations as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['ID_REPARATION']); ?></td>
                    <td><?php echo htmlspecialchars($r['DATE_REP']); ?></td>
                    <td>
                        <a href="../enclos/view.php?id=<?php echo urlencode($r['ID_ENCLO']); ?>">
                            Enclos n°<?php echo htmlspecialchars($r['ID_ENCLO']); ?> (<?php echo htmlspecialchars($r['NOM_ZONE']); ?>)
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($r['NOM_PRESTATAIRE']); ?></td>
                    <td><?php echo htmlspecialchars($r['PRENOM_PERSONNEL'].' '.$r['NOM_PERSONNEL']); ?></td>
                    <td><?php echo htmlspecialchars($r['NATURE_REPARATION']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($reparations)): ?>
                <tr><td colspan="6">Aucune réparation enregistrée.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>