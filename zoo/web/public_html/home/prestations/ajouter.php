<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $description = $_POST['description'];
    $niveau = $_POST['niveau_requis'];

    if (empty($description) || empty($niveau)) {
        $error = true;
        $message = "Veuillez remplir la fiche de l'activité.";
    } else {
        $stmtId = oci_parse($conn, "SELECT NVL(MAX(id_prestation), 0) + 1 AS new_id FROM Prestation");
        oci_execute($stmtId, OCI_DEFAULT);
        $rowId = oci_fetch_assoc($stmtId);
        $newId = $rowId['NEW_ID'];
        oci_free_statement($stmtId);

        $sql = "INSERT INTO Prestation (id_prestation, description_prestation, niveau_requis)
                VALUES (:id, :desc_p, :niv)";
        
        $ins = oci_parse($conn, $sql);
        oci_bind_by_name($ins, ':id', $newId);
        oci_bind_by_name($ins, ':desc_p', $description);
        oci_bind_by_name($ins, ':niv', $niveau);

        $r = @oci_execute($ins, OCI_COMMIT_ON_SUCCESS);
        if ($r) {
            $message = "Prestation insérée au catalogue ! (ID : $newId)";
            $error = false;
        } else {
            $e = oci_error($ins);
            $message = "Erreur : " . htmlentities($e['message']);
            $error = true;
        }
        oci_free_statement($ins);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter Prestation</title>
</head>
<body>
    <a href="index.php">← Retour au catalogue des Prestations</a>
    <h1>Concevoir une Nouvelle Prestation</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <p>
            <label>Description du Service/Activité :<br>
            <input type="text" name="description" required maxlength="250" style="width: 350px;">
            </label>
        </p>

        <p>
            <label>Niveau de Parrainage Requis pour y participer :<br>
            <select name="niveau_requis" required>
                <option value="bronze">Bronze (Pass de base)</option>
                <option value="argent">Argent (Pass intermédiaire)</option>
                <option value="or">Or (Pass Premium)</option>
            </select>
            </label>
        </p>

        <p>
            <button type="submit" style="background:green; color:white; padding:10px 15px; cursor:pointer;">Valider l'offre</button>
        </p>
    </form>
</body>
</html>