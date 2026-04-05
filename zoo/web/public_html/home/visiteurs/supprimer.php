<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$id_visiteur = $_GET['id'] ?? null;

if (zoo/database/init.sqlid_visiteur) {
    header("Location: index.php");
    exit;
}

$check_sql = "SELECT id_visiteur, nom_visiteur, prenom_visiteur FROM Visiteur WHERE id_visiteur = :id";
$chk_st = oci_parse($conn, $check_sql);
oci_bind_by_name($chk_st, ':id', $id_visiteur);
oci_execute($chk_st, OCI_DEFAULT);

$visiteur = oci_fetch_assoc($chk_st);
oci_free_statement($chk_st);

if (zoo/database/init.sqlvisiteur) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    
    // Attribuer (qui dépendent du parrainage du visiteur)
    $del = oci_parse($conn, "DELETE FROM Attribuer WHERE id_parrainage IN (SELECT id_parrainage FROM Parrainage WHERE id_visiteur = :id)");
    oci_bind_by_name($del, ':id', $id_visiteur);
    @oci_execute($del, OCI_DEFAULT);
    oci_free_statement($del);

    // Parrainages
    $del = oci_parse($conn, "DELETE FROM Parrainage WHERE id_visiteur = :id");
    oci_bind_by_name($del, ':id', $id_visiteur);
    @oci_execute($del, OCI_DEFAULT);
    oci_free_statement($del);

    // Billet
    $del = oci_parse($conn, "DELETE FROM Billet WHERE id_visiteur = :id");
    oci_bind_by_name($del, ':id', $id_visiteur);
    @oci_execute($del, OCI_DEFAULT);
    oci_free_statement($del);

    // Achat en boutique (Acheter_Boutique / Visiteur_Boutique ?) => on ignore si non existant.
    // L'essentiel est parrainage.

    // Visiteur
    $del_visiteur = "DELETE FROM Visiteur WHERE id_visiteur = :id";
    $st = oci_parse($conn, $del_visiteur);
    oci_bind_by_name($st, ':id', $id_visiteur);
    
    $success = @oci_execute($st, OCI_COMMIT_ON_SUCCESS);
    if ($success) {
        header("Location: index.php?msg=deleted");
        exit;
    } else {
        $e = oci_error($st);
        $error = "Erreur de suppression du visiteur : " . htmlentities($e['message']);
    }
    oci_free_statement($st);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer un visiteur</title>
</head>
<body>
    <a href="index.php">← Annuler et retourner à la liste</a>
    <h1 style="color: red;">Zone Dangereuse : Suppression</h1>

    <p>Êtes-vous sûr de vouloir supprimer de la base de données le visiteur n° <b><?php echo htmlspecialchars($visiteur['ID_VISITEUR']); ?></b> (<?php echo htmlspecialchars($visiteur['PRENOM_VISITEUR'] . ' ' . strtoupper($visiteur['NOM_VISITEUR'])); ?>) ?</p>
    <p><b>Attention :</b> Cette opération supprimera également l'historique complet de ses parrainages pour des animaux.</p>

    <?php if (isset($error)): ?>
        <p style="color: red; font-weight: bold;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="confirm_delete" value="1">
        <button type="submit" style="color: white; background-color: red; padding: 10px; font-weight: bold; border: 2px solid darkred; cursor: pointer;">
            Oui, supprimer définitivement ce visiteur
        </button>
    </form>
</body>
</html>
