<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$id_pers = $_GET['id'] ?? null;

if (!$id_pers) {
    header("Location: index.php");
    exit;
}

$query = "SELECT id_personnel, nom_personnel, prenom_personnel, type_personnel, salaire_personnel, 
                 TO_CHAR(date_entree_personnel, 'DD/MM/YYYY') as date_entree 
          FROM Personnel 
          WHERE id_personnel = :id";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':id', $id_pers);
oci_execute($stmt, OCI_DEFAULT);
$employe = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$employe) {
    die("Employé introuvable.");
}

// Données additionnelles selon le poste
$animaux_charge = [];
if ($employe['TYPE_PERSONNEL'] === 'soignant') {
    $q_animaux = "SELECT id_animaux, nom_animal FROM Animaux WHERE id_personnel = :id ORDER BY nom_animal";
    $st = oci_parse($conn, $q_animaux);
    oci_bind_by_name($st, ':id', $id_pers);
    oci_execute($st, OCI_DEFAULT);
    while ($row = oci_fetch_assoc($st)) { $animaux_charge[] = $row; }
    oci_free_statement($st);
}

$soins_realises = [];
if ($employe['TYPE_PERSONNEL'] === 'veterinaire') {
    $q_soins = "SELECT id_soin, TO_CHAR(date_soin, 'DD/MM/YYYY') as date_soin, type_soin FROM Soins WHERE id_personnel = :id ORDER BY date_soin DESC FETCH FIRST 10 ROWS ONLY";
    $st = oci_parse($conn, $q_soins);
    oci_bind_by_name($st, ':id', $id_pers);
    oci_execute($st, OCI_DEFAULT);
    while ($row = oci_fetch_assoc($st)) { $soins_realises[] = $row; }
    oci_free_statement($st);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche Employé <?php echo htmlspecialchars($employe['NOM_PERSONNEL']); ?></title>
</head>
<body>
    <a href="index.php">← Retour à la liste du personnel</a>
    <h1>Dossier Employé : <?php echo htmlspecialchars($employe['PRENOM_PERSONNEL'] . ' ' . strtoupper($employe['NOM_PERSONNEL'])); ?></h1>

    <div style="border: 1px solid #ccc; padding: 20px; font-size: 16px;">
        <p><strong>Matricule (ID) : </strong><?php echo htmlspecialchars($employe['ID_PERSONNEL']); ?></p>
        <p><strong>Rôle / Profession : </strong><?php echo htmlspecialchars($employe['TYPE_PERSONNEL']); ?></p>
        <p><strong>Date d'embauche : </strong><?php echo htmlspecialchars($employe['DATE_ENTREE']); ?></p>
        <p><strong>Salaire mensuel : </strong><?php echo htmlspecialchars($employe['SALAIRE_PERSONNEL']); ?> €</p>
    </div>

    <!-- Affichage dynamique selon le métier -->
    <?php if ($employe['TYPE_PERSONNEL'] === 'soignant'): ?>
        <h3>Animaux sous sa responsabilité directe (<?php echo count($animaux_charge); ?>)</h3>
        <ul>
            <?php foreach($animaux_charge as $a): ?>
                <li><a href="../animaux/view.php?id=<?php echo urlencode($a['ID_ANIMAUX']); ?>">
                    <?php echo htmlspecialchars($a['NOM_ANIMAL']) . ' (ID #'.$a['ID_ANIMAUX'].')'; ?>
                </a></li>
            <?php endforeach; ?>
            <?php if (empty($animaux_charge)) echo "<li>Aucun animal affilié.</li>"; ?>
        </ul>
    <?php endif; ?>

    <?php if ($employe['TYPE_PERSONNEL'] === 'veterinaire'): ?>
        <h3>10 Derniers soins réalisés</h3>
        <ul>
            <?php foreach($soins_realises as $s): ?>
                <li><a href="../soins/view.php?id=<?php echo urlencode($s['ID_SOIN']); ?>">
                    [<?php echo htmlspecialchars($s['DATE_SOIN']); ?>] <?php echo htmlspecialchars($s['TYPE_SOIN']); ?>
                </a></li>
            <?php endforeach; ?>
            <?php if (empty($soins_realises)) echo "<li>Aucune intervention médicale comptabilisée.</li>"; ?>
        </ul>
    <?php endif; ?>

    <p style="margin-top:40px;">
        <a href="modifier.php?id=<?php echo urlencode($id_pers); ?>" style="padding:5px 10px; background:orange; color:black; text-decoration:none;">Modifier la fiche employeur</a>
        <a href="historique.php?id=<?php echo urlencode($id_pers); ?>" style="padding:5px 10px; background:#2196F3; color:white; text-decoration:none;">Consulter historique</a>
    </p>
</body>
</html>