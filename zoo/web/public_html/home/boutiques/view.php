<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$id_boutique = $_GET['id'] ?? null;

if (!$id_boutique) {
    header("Location: index.php");
    exit;
}

// Détails de la boutique
$query = "SELECT b.id_boutique, b.type_boutique, z.nom_zone, b.id_zone
          FROM Boutique b
          JOIN Zone z ON b.id_zone = z.id_zone
          WHERE b.id_boutique = :id";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':id', $id_boutique);
oci_execute($stmt, OCI_DEFAULT);
$boutique = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$boutique) {
    die("Boutique introuvable.");
}

// Chiffre d'affaires
$q_ca = "SELECT id_ca, TO_CHAR(date_ca, 'DD/MM/YYYY') as DATE_CA, montant_ca 
         FROM Chiffre_affaire 
         WHERE id_boutique = :id 
         ORDER BY date_ca DESC";
$s_ca = oci_parse($conn, $q_ca);
oci_bind_by_name($s_ca, ':id', $id_boutique);
oci_execute($s_ca, OCI_DEFAULT);
$cas = [];
while ($row = oci_fetch_assoc($s_ca)) { $cas[] = $row; }
oci_free_statement($s_ca);

// Personnel assigné
$q_pers = "SELECT p.nom_personnel, p.prenom_personnel, TO_CHAR(pb.date_debut, 'DD/MM/YYYY') as DATE_DEBUT, TO_CHAR(pb.date_fin, 'DD/MM/YYYY') as DATE_FIN
           FROM Personnel_Boutique pb
           JOIN Personnel p ON pb.id_personnel = p.id_personnel
           WHERE pb.id_boutique = :id
           ORDER BY pb.date_debut DESC";
$s_pers = oci_parse($conn, $q_pers);
oci_bind_by_name($s_pers, ':id', $id_boutique);
oci_execute($s_pers, OCI_DEFAULT);
$personnel = [];
while ($row = oci_fetch_assoc($s_pers)) { $personnel[] = $row; }
oci_free_statement($s_pers);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de la Boutique</title>
</head>
<body>
    <a href="index.php">← Retour aux boutiques</a>
    <h1>Détails Boutique n°<?php echo htmlspecialchars($boutique['ID_BOUTIQUE']); ?></h1>

    <p>
        <strong>Type :</strong> <?php echo htmlspecialchars($boutique['TYPE_BOUTIQUE']); ?><br>
        <strong>Emplacement :</strong> <?php echo htmlspecialchars($boutique['NOM_ZONE']); ?>
    </p>

    <a href="modifier.php?id=<?php echo urlencode($id_boutique); ?>" style="display:inline-block; margin-bottom: 20px; padding:8px 12px; background:orange; color:black; text-decoration:none;">Modifier la boutique</a>
    <a href="ajouter_ca.php?id=<?php echo urlencode($id_boutique); ?>" style="display:inline-block; margin-bottom: 20px; padding:8px 12px; background:green; color:white; text-decoration:none;">+ Saisir du Chiffre d'Affaires</a>
    <a href="affecter_personnel.php?id=<?php echo urlencode($id_boutique); ?>" style="display:inline-block; margin-bottom: 20px; padding:8px 12px; background:#007BFF; color:white; text-decoration:none;">Affecter du Personnel</a>

    <hr>

    <h2>Historique des Chiffres d'Affaires</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Date</th>
                <th>Montant (€)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cas as $ca): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ca['DATE_CA']); ?></td>
                    <td><?php echo htmlspecialchars($ca['MONTANT_CA']); ?> €</td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($cas)): ?>
                <tr><td colspan="2">Aucun chiffre d'affaires enregistré.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <hr>

    <h2>Personnel Assigné</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Employé</th>
                <th>Date début</th>
                <th>Date fin</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($personnel as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['PRENOM_PERSONNEL'] . ' ' . $p['NOM_PERSONNEL']); ?></td>
                    <td><?php echo htmlspecialchars($p['DATE_DEBUT']); ?></td>
                    <td><?php echo htmlspecialchars($p['DATE_FIN'] ?? 'En poste'); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($personnel)): ?>
                <tr><td colspan="3">Aucun personnel enregistré pour cette boutique.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>