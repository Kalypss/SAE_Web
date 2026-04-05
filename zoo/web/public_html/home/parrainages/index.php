<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT par.id_parrainage, par.niveau, par.contribution, 
                 TO_CHAR(par.date_debut, 'DD/MM/YYYY') as DATE_DEBUT, 
                 v.nom_visiteur, v.prenom_visiteur, 
                 a.id_animaux, a.nom_animal 
          FROM Parrainage par
          JOIN Animaux a ON par.id_animaux = a.id_animaux
          JOIN Visiteur v ON par.id_visiteur = v.id_visiteur
          ORDER BY par.date_debut DESC";

$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);

$parrainages = [];
while ($row = oci_fetch_assoc($stmt)) {
    $parrainages[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleindex.css">
    <title>Sponsors et Parrainages</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Liste des Parrainages</h1>

    <a href="ajouter.php">+ Enregistrer un Parrain</a>
    <br>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Donateur (Visiteur)</th>
                <th>Animal Filleul</th>
                <th>Niveau d'Engagement</th>
                <th>Montant Versé (€)</th>
                <th>Date du début</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($parrainages as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['ID_PARRAINAGE']); ?></td>
                    <td><?php echo htmlspecialchars($p['PRENOM_VISITEUR'] . ' ' . $p['NOM_VISITEUR']); ?></td>
                    <td><?php echo htmlspecialchars($p['NOM_ANIMAL']); ?> (ID: <?php echo htmlspecialchars($p['ID_ANIMAUX']); ?>)</td>
                    <td>
                        <strong style="color: <?php 
                            if($p['NIVEAU']=='or') echo 'gold';
                            elseif($p['NIVEAU']=='argent') echo 'gray';
                            elseif($p['NIVEAU']=='bronze') echo 'brown';
                            else echo 'black';
                        ?>">
                            <?php echo htmlspecialchars(ucfirst($p['NIVEAU'])); ?>
                        </strong>
                    </td>
                    <td><?php echo htmlspecialchars($p['CONTRIBUTION']); ?> €</td>
                    <td><?php echo htmlspecialchars($p['DATE_DEBUT']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($parrainages)): ?>
                <tr><td colspan="6">Aucun parrainage.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>