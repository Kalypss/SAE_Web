<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$id_pers = $_GET['id'] ?? null;

if (!$id_pers) {
    header("Location: index.php");
    exit;
}

$query = "SELECT he.id_historique, he.type_poste, he.salaire, 
                 TO_CHAR(he.date_debut, 'DD/MM/YYYY') as d_deb, 
                 TO_CHAR(he.date_fin, 'DD/MM/YYYY') as d_fin,
                 p.nom_personnel, p.prenom_personnel 
          FROM Historique_emploi he
          JOIN Personnel p ON he.id_personnel = p.id_personnel
          WHERE he.id_personnel = :id
          ORDER BY he.date_debut DESC";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':id', $id_pers);
oci_execute($stmt, OCI_DEFAULT);
$historique = [];
while ($row = oci_fetch_assoc($stmt)) {
    $historique[] = $row;
}
oci_free_statement($stmt);

// Info employé minimal
$employe = !empty($historique) 
           ? $historique[0]['PRENOM_PERSONNEL'].' '.$historique[0]['NOM_PERSONNEL']
           : "Nom inconnu (ou jamais embauché)"; 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique d'employé</title>
</head>
<body>
    <a href="index.php">← Revenir au personnel</a>
    <h1>Avancements et Contrats de <?php echo htmlspecialchars($employe); ?></h1>

    <p style="margin-bottom:20px;">
        <a href="javascript:alert('À implémenter: Créer une entrée dans le journal');">+ Déclarer une prime, poste temporaire ou avancement</a>
    </p>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Job Id</th>
                <th>Titre du poste ou fonction (Statut)</th>
                <th>Début du contrat</th>
                <th>Fin du contrat</th>
                <th>Salaire enregistré</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($historique as $h): ?>
                <tr>
                    <td><?php echo htmlspecialchars($h['ID_HISTORIQUE']); ?></td>
                    <td><strong><?php echo htmlspecialchars($h['TYPE_POSTE']); ?></strong></td>
                    <td><?php echo htmlspecialchars($h['D_DEB']); ?></td>
                    <td><?php echo $h['D_FIN'] ? htmlspecialchars($h['D_FIN']) : '<span style="color:green">Actuellement en poste</span>'; ?></td>
                    <td><?php echo htmlspecialchars($h['SALAIRE']); ?> €</td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($historique)): ?>
                <tr><td colspan="5">Aucun historique détaillé enregistré pour cet employé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>