<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$id_espece = $_GET['id'] ?? null;

if (!$id_espece) {
    header("Location: index.php");
    exit;
}

$query = "SELECT e.id_espece, e.nomU_espece, e.nomL_espece, e.est_menace
          FROM Especes e
          WHERE e.id_espece = :id";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':id', $id_espece);
oci_execute($stmt, OCI_DEFAULT);
$espece = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$espece) {
    die("Espèce introuvable.");
}

// Récupérer les animaux de cette espèce
$q_animaux = "SELECT id_animaux, nom_animal FROM Animaux WHERE id_espece = :id_espece ORDER BY nom_animal";
$stmt = oci_parse($conn, $q_animaux);
oci_bind_by_name($stmt, ':id_espece', $id_espece);
oci_execute($stmt, OCI_DEFAULT);
$animaux_espece = [];
while ($row = oci_fetch_assoc($stmt)) { $animaux_espece[] = $row; }
oci_free_statement($stmt);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espèce : <?php echo htmlspecialchars($espece['NOMU_ESPECE']); ?></title>
</head>
<body>
    <a href="index.php">← Retour au dictionnaire des Espèces</a>
    <h1>Détail de l'Espèce n°<?php echo htmlspecialchars($espece['ID_ESPECE']); ?></h1>

    <div style="border: 1px solid #ccc; padding: 20px; font-size: 16px;">
        <p><strong>Nom Usuel : </strong><?php echo htmlspecialchars($espece['NOMU_ESPECE']); ?></p>
        <p><strong>Nom Scientifique (Latin) : </strong><i><?php echo htmlspecialchars($espece['NOML_ESPECE']); ?></i></p>
        <p><strong>Statut UICN : </strong>
            <?php if ($espece['EST_MENACE'] == 1): ?>
                <span style="color:red; font-weight:bold;">Espèce Menacée / Protégée</span>
            <?php else: ?>
                Préoccupation mineure (Non menacée)
            <?php endif; ?>
        </p>
    </div>

    <h3>Animaux appartenant à cette espèce (<?php echo count($animaux_espece); ?>)</h3>
    <ul>
        <?php foreach ($animaux_espece as $a): ?>
            <li>
                <a href="../animaux/view.php?id=<?php echo urlencode($a['ID_ANIMAUX']); ?>">
                    <?php echo htmlspecialchars($a['NOM_ANIMAL']); ?> (ID #<?php echo htmlspecialchars($a['ID_ANIMAUX']); ?>)
                </a>
            </li>
        <?php endforeach; ?>
        <?php if (empty($animaux_espece)): ?>
            <li>Aucun spécimen pour le moment au zoo.</li>
        <?php endif; ?>
    </ul>

    <p style="margin-top:40px;">
        <a href="modifier.php?id=<?php echo urlencode($espece['ID_ESPECE']); ?>" style="padding:5px 10px; background:orange; color:black; text-decoration:none;">Modifier l'espèce</a>
        <a href="supprimer.php?id=<?php echo urlencode($espece['ID_ESPECE']); ?>" style="padding:5px 10px; background:red; color:white; text-decoration:none;">Supprimer</a>
    </p>

</body>
</html>