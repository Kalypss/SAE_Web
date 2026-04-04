<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT p.id_personnel, p.nom_personnel, p.prenom_personnel, p.type_personnel, p.salaire_personnel, 
                 TO_CHAR(p.date_entree_personnel, 'DD/MM/YYYY') as date_entree 
          FROM Personnel p
          ORDER BY p.id_personnel";
$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);
$personnel = [];
while ($row = oci_fetch_assoc($stmt)) {
    $personnel[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Membres du Personnel</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Liste des Employés</h1>

    <a href="ajouter.php" style="display:inline-block; margin-bottom:15px; padding:8px 12px; background:#4CAF50; color:white; text-decoration:none;">+ Embaucher un employé</a>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Corps de métier (Rôle)</th>
                <th>Ancienneté (Embauche)</th>
                <th>Salaire (€ mensuel)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($personnel as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['ID_PERSONNEL']); ?></td>
                    <td><strong><?php echo htmlspecialchars(strtoupper($p['NOM_PERSONNEL'])); ?></strong></td>
                    <td><?php echo htmlspecialchars($p['PRENOM_PERSONNEL']); ?></td>
                    <td><?php echo htmlspecialchars($p['TYPE_PERSONNEL']); ?></td>
                    <td><?php echo htmlspecialchars($p['DATE_ENTREE']); ?></td>
                    <td><?php echo htmlspecialchars($p['SALAIRE_PERSONNEL']); ?> €</td>
                    <td>
                        <a href="view.php?id=<?php echo urlencode($p['ID_PERSONNEL']); ?>">Fiche Employé</a>
                        | <a href="historique.php?id=<?php echo urlencode($p['ID_PERSONNEL']); ?>">Historique d'emplois</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($personnel)): ?>
                <tr><td colspan="7">Aucun employé dans la base de données.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>