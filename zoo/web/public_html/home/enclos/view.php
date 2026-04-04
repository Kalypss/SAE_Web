<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$id_enclo = $_GET['id'] ?? null;

if (!$id_enclo) {
    header("Location: index.php");
    exit;
}

// Détails de l'enclos
$query = "SELECT e.id_enclo, e.surface_enclo, e.latitude_enclo, e.longitude_enclo, z.id_zone, z.nom_zone 
          FROM Enclos e 
          JOIN Zone z ON e.id_zone = z.id_zone 
          WHERE e.id_enclo = :id";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':id', $id_enclo);
oci_execute($stmt, OCI_DEFAULT);
$enclos = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$enclos) {
    die("Enclos introuvable.");
}

// Les animaux de cet enclos
$q_animaux = "SELECT id_animaux, nom_animal FROM Animaux WHERE id_enclo = :id_enclo ORDER BY nom_animal";
$stmt = oci_parse($conn, $q_animaux);
oci_bind_by_name($stmt, ':id_enclo', $id_enclo);
oci_execute($stmt, OCI_DEFAULT);
$animaux = [];
while ($row = oci_fetch_assoc($stmt)) { $animaux[] = $row; }
oci_free_statement($stmt);

// Dernières réparations de cet enclos
$q_rep = "SELECT TO_CHAR(r.date_reparation, 'DD/MM/YYYY') as date_rep, r.nature_reparation, p.nom_prestataire 
          FROM Reparation r 
          JOIN Prestataire p ON r.id_prestataire = p.id_prestataire 
          WHERE r.id_enclo = :id_enclo 
          ORDER BY r.date_reparation DESC FETCH FIRST 5 ROWS ONLY";
$stmt = oci_parse($conn, $q_rep);
oci_bind_by_name($stmt, ':id_enclo', $id_enclo);
oci_execute($stmt, OCI_DEFAULT);
$reparations = [];
while ($row = oci_fetch_assoc($stmt)) { $reparations[] = $row; }
oci_free_statement($stmt);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche Enclos #<?php echo htmlspecialchars($id_enclo); ?></title>
</head>
<body>
    <a href="index.php">← Retour aux enclos</a>
    <h1>Détail de l'Enclos n°<?php echo htmlspecialchars($enclos['ID_ENCLO']); ?></h1>

    <div style="border: 1px solid #ccc; padding: 20px; font-size: 16px;">
        <p><strong>Zone du parc : </strong><?php echo htmlspecialchars($enclos['NOM_ZONE'] . ' (ID Zone: ' . $enclos['ID_ZONE'] . ')'); ?></p>
        <p><strong>Surface : </strong><?php echo htmlspecialchars($enclos['SURFACE_ENCLO']); ?> m²</p>
        <p><strong>Coordonnées spatiales (GPS) : </strong>[<?php echo htmlspecialchars($enclos['LATITUDE_ENCLO']); ?> , <?php echo htmlspecialchars($enclos['LONGITUDE_ENCLO']); ?>]</p>
    </div>

    <h3>Animaux résidents (<?php echo count($animaux); ?>)</h3>
    <ul>
        <?php foreach ($animaux as $a): ?>
            <li>
                <a href="../animaux/view.php?id=<?php echo urlencode($a['ID_ANIMAUX']); ?>">
                    <?php echo htmlspecialchars($a['NOM_ANIMAL'].' (#'.$a['ID_ANIMAUX'].')'); ?>
                </a>
            </li>
        <?php endforeach; ?>
        <?php if (empty($animaux)): ?>
            <li>Cet enclos est actuellement vide.</li>
        <?php endif; ?>
    </ul>

    <h3>Dernières Interventions / Réparations</h3>
    <ul>
        <?php foreach ($reparations as $r): ?>
            <li>
                [<?php echo htmlspecialchars($r['DATE_REP']); ?>] 
                <strong><?php echo htmlspecialchars($r['NATURE_REPARATION']); ?></strong> 
                (réalisé par <i><?php echo htmlspecialchars($r['NOM_PRESTATAIRE']); ?></i>)
            </li>
        <?php endforeach; ?>
        <?php if (empty($reparations)): ?>
            <li>Aucune réparation enregistrée pour cet enclos.</li>
        <?php endif; ?>
    </ul>

    <p style="margin-top:40px;">
        <a href="modifier.php?id=<?php echo urlencode($id_enclo); ?>" style="padding:5px 10px; background:orange; color:black; text-decoration:none;">Modifier l'enclos</a>
        <!-- Suppression déconseillée si des animaux/réparations en dépendent, gérer par contraintes si besoin -->
    </p>

</body>
</html>