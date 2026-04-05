<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$query = "SELECT id_visiteur, nom_visiteur, prenom_visiteur, Email_visiteur 
          FROM Visiteur 
          ORDER BY nom_visiteur ASC";

$stmt = oci_parse($conn, $query);
oci_execute($stmt, OCI_DEFAULT);

$visiteurs = [];
while ($row = oci_fetch_assoc($stmt)) {
    $visiteurs[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleindex.css">
    <title>Base des Visiteurs</title>
</head>
<body>
    <a href="../../dashboard.php">← Retour au Dashboard</a>
    <h1>Liste des Visiteurs (Base Clientèle)</h1>

    <a href="ajouter.php">+ Inscrire un Visiteur</a>
    <br>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Adresse E-mail</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($visiteurs as $v): ?>
                <tr>
                    <td><?php echo htmlspecialchars($v['ID_VISITEUR']); ?></td>
                    <td><?php echo htmlspecialchars($v['NOM_VISITEUR']); ?></td>
                    <td><?php echo htmlspecialchars($v['PRENOM_VISITEUR']); ?></td>
                    <td><a href="mailto:<?php echo htmlspecialchars($v['EMAIL_VISITEUR']); ?>"><?php echo htmlspecialchars($v['EMAIL_VISITEUR']); ?></a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($visiteurs)): ?>
                <tr><td colspan="4">Aucun visiteur dans la base de données.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>