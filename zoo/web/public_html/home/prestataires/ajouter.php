<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom_prestataire']);
    $spec = trim($_POST['specialite_prestataire']);

    if (empty($nom) || empty($spec)) {
        $error = true;
        $message = "Veuillez remplir tous les champs.";
    } else {
        $sql_id = "SELECT NVL(MAX(id_prestataire), 0) + 1 AS next_id FROM Prestataire";
        $stmt_id = oci_parse($conn, $sql_id);
        oci_execute($stmt_id, OCI_DEFAULT);
        $row_id = oci_fetch_assoc($stmt_id);
        $next_id = $row_id['NEXT_ID'];
        oci_free_statement($stmt_id);

        $sql = "INSERT INTO Prestataire (id_prestataire, nom_prestataire, specialite_prestataire) 
                VALUES (:id_prestataire, :nom, :spec)";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':id_prestataire', $next_id);
        oci_bind_by_name($stmt, ':nom', $nom);
        oci_bind_by_name($stmt, ':spec', $spec);

        $r = @oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
        if ($r) {
            $message = "Nouveau prestataire ajouté avec succès.";
            $nom = $spec = ''; // reset the form values if needed
        } else {
            $e = oci_error($stmt);
            $error = true;
            $message = "Erreur lors de l'ajout : " . htmlentities($e['message']);
        }
        oci_free_statement($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleajout.css">
    <title>Ajouter Prestataire</title>
</head>
<body>
    <a href="index.php">← Retour aux prestataires</a>
    <h1>Déclarer un nouveau partenaire externe</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <div class="formul">
        <p>
            <label>Nom de l'entreprise ou Artisan * :<br>
            <input type="text" name="nom_prestataire" required size="40">
            </label>
        </p>

        <p>
            <label>Spécialité Principale * :<br>
            <input type="text" name="specialite_prestataire" required placeholder="Ex: Plomberie, Climatisation, Menuiserie..." size="40">
            </label>
        </p>
        </div>
        <p class="bouton"><button type="submit">Enregistrer ce prestataire</button></p>
    </form>
</body>
</html>