<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$id_soin = $_GET['id'] ?? null;

if (!$id_soin) {
    header("Location: index.php");
    exit;
}

$query = "SELECT s.id_soin, TO_CHAR(s.date_soin, 'YYYY-MM-DD') as date_db, s.type_soin, s.description_soin, 
                 a.id_animaux, a.nom_animal, p.id_personnel, p.nom_personnel, p.prenom_personnel, p.type_personnel
          FROM Soins s
          JOIN Animaux a ON s.id_animaux = a.id_animaux
          JOIN Personnel p ON s.id_personnel = p.id_personnel
          WHERE s.id_soin = :id";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':id', $id_soin);
oci_execute($stmt, OCI_DEFAULT);
$soin = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$soin) {
    die("Soin introuvable.");
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du Soin #<?php echo htmlspecialchars($id_soin); ?></title>
</head>
<body>
    <a href="index.php">← Retour à la liste des soins</a>
    <h1>Détail du Soin n°<?php echo htmlspecialchars($soin['ID_SOIN']); ?></h1>

    <div style="border: 1px solid #ccc; padding: 20px; font-size: 16px;">
        <p><strong>Date de l'acte médical : </strong><?php echo htmlspecialchars($soin['DATE_DB']); ?></p>
        <p>
            <strong>Patient (Animal) : </strong> 
            <a href="../animaux/view.php?id=<?php echo urlencode($soin['ID_ANIMAUX']); ?>">
                <?php echo htmlspecialchars($soin['NOM_ANIMAL']) . ' (ID: ' . $soin['ID_ANIMAUX'] . ')'; ?>
            </a>
        </p>
        <p>
            <strong>Praticien : </strong>
            <?php echo htmlspecialchars($soin['PRENOM_PERSONNEL'].' '.$soin['NOM_PERSONNEL'].' ['.$soin['TYPE_PERSONNEL'].']'); ?> 
            (ID: <?php echo htmlspecialchars($soin['ID_PERSONNEL']); ?>)
        </p>
        <p><strong>Type de soin (Classification) : </strong><?php echo htmlspecialchars($soin['TYPE_SOIN']); ?></p>

        <p><strong>Rapport / Compte-Rendu :</strong></p>
        <blockquote style="background: #f9f9f9; padding: 15px; border-left: 5px solid #ccc;">
            <?php echo nl2br(htmlspecialchars($soin['DESCRIPTION_SOIN'])); ?>
        </blockquote>
    </div>

    <p>
        <a href="modifier.php?id=<?php echo urlencode($soin['ID_SOIN']); ?>" style="padding:5px 10px; background:orange; color:black; text-decoration:none;">Modifier l'entrée</a>
        <a href="supprimer.php?id=<?php echo urlencode($soin['ID_SOIN']); ?>" style="padding:5px 10px; background:red; color:white; text-decoration:none;">Supprimer</a>
    </p>

</body>
</html>