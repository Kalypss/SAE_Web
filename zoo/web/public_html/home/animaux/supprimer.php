<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$id_animal = $_GET['id'] ?? null;

if (!$id_animal) {
    header("Location: /public_html/home/animaux/index.php");
    exit;
}

$check_sql = "SELECT id_animaux, nom_animal FROM Animaux WHERE id_animaux = :id";
$chk_st = oci_parse($conn, $check_sql);
oci_bind_by_name($chk_st, ':id', $id_animal);
oci_execute($chk_st, OCI_DEFAULT);

$animal = oci_fetch_assoc($chk_st);
oci_free_statement($chk_st);

if (!$animal) {
    header("Location: /public_html/home/animaux/index.php");
    exit;
}

// Confirmation de suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    
    
    // Soins
    $del = oci_parse($conn, "DELETE FROM Soins WHERE id_animaux = :id");
    oci_bind_by_name($del, ':id', $id_animal);
    @oci_execute($del, OCI_DEFAULT);
    oci_free_statement($del);

    // Alimentation
    $del = oci_parse($conn, "DELETE FROM Alimentation WHERE id_animaux = :id");
    oci_bind_by_name($del, ':id', $id_animal);
    @oci_execute($del, OCI_DEFAULT);
    oci_free_statement($del);

    // Parenté : Mère
    $del = oci_parse($conn, "DELETE FROM Est_mere_de WHERE id_parent = :id OR id_enfant = :id");
    oci_bind_by_name($del, ':id', $id_animal);
    @oci_execute($del, OCI_DEFAULT);
    oci_free_statement($del);

    // Parenté : Père
    $del = oci_parse($conn, "DELETE FROM Est_pere_de WHERE id_parent = :id OR id_enfant = :id");
    oci_bind_by_name($del, ':id', $id_animal);
    @oci_execute($del, OCI_DEFAULT);
    oci_free_statement($del);

    // Attribuer (lié au parrainage)
    $del = oci_parse($conn, "DELETE FROM Attribuer WHERE id_parrainage IN (SELECT id_parrainage FROM Parrainage WHERE id_animaux = :id)");
    oci_bind_by_name($del, ':id', $id_animal);
    @oci_execute($del, OCI_DEFAULT);
    oci_free_statement($del);

    // Parrainage
    $del = oci_parse($conn, "DELETE FROM Parrainage WHERE id_animaux = :id");
    oci_bind_by_name($del, ':id', $id_animal);
    @oci_execute($del, OCI_DEFAULT);
    oci_free_statement($del);

    $del_animal = "DELETE FROM Animaux WHERE id_animaux = :id";
    $st = oci_parse($conn, $del_animal);
    oci_bind_by_name($st, ':id', $id_animal);
    
    $success = oci_execute($st, OCI_COMMIT_ON_SUCCESS);
    if ($success) {
        header("Location: /public_html/home/animaux/index.php?msg=deleted");
        exit;
    } else {
        $e = oci_error($st);
        $error = "Erreur de suppression : " . htmlentities($e['message']);
    }
    oci_free_statement($st);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer un animal</title>
</head>
<body>
    <a href="/public_html/home/animaux/view.php?id=<?php echo urlencode($id_animal); ?>">← Annuler et retourner à la fiche</a>

    <h1 style="color: red;">Zone Dangereuse : Suppression</h1>

    <p>Êtes-vous sûr de vouloir supprimer l'animal n° <b><?php echo htmlspecialchars($animal['ID_ANIMAUX']); ?></b> (Nom : <i><?php echo htmlspecialchars($animal['NOM_ANIMAL'] ?? 'Inconnu'); ?></i>) ?</p>
    <p><b>Attention :</b> Cette opération supprimera également l'historique de ses repas, ses soins, ainsi que ses liens de parenté de manière définitive.</p>
    
    <?php if (isset($error)): ?>
        <p style="color: red; font-weight: bold;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="confirm_delete" value="1">
        <button type="submit" style="color: white; background-color: red; padding: 10px; font-weight: bold; border: 2px solid darkred;">
            Oui, supprimer définitivement cet animal
        </button>
    </form>
</body>
</html>