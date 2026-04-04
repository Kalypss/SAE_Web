<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_zone = trim($_POST['nom_zone']);

    if (empty($nom_zone)) {
        $error = true;
        $message = "Le nom de la zone est obligatoire.";
    } else {
        $stmtId = oci_parse($conn, "SELECT NVL(MAX(id_zone), 0) + 1 AS new_id FROM Zone");
        oci_execute($stmtId, OCI_DEFAULT);
        $rowId = oci_fetch_assoc($stmtId);
        $newId = $rowId['NEW_ID'];
        oci_free_statement($stmtId);

        $sql = "INSERT INTO Zone (id_zone, nom_zone) VALUES (:id, :nom)";
        $ins = oci_parse($conn, $sql);
        oci_bind_by_name($ins, ':id', $newId);
        oci_bind_by_name($ins, ':nom', $nom_zone);

        $r = @oci_execute($ins, OCI_COMMIT_ON_SUCCESS);
        if ($r) {
            $message = "Secteur (Zone) créé(e) avec succès ! (ID : $newId)";
            $error = false;
        } else {
            $e = oci_error($ins);
            $message = "Erreur (probablement une zone avec le même nom existe déjà) : " . htmlentities($e['message']);
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
    <title>Ajouter Zone</title>
</head>
<body>
    <a href="index.php">← Retour aux Secteurs (Zones)</a>
    <h1>Ajouter un nouveau secteur (Zone)</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <p>
            <label>Nom unique du secteur géographique :<br>
            <input type="text" name="nom_zone" required maxlength="250" placeholder="Ex: Savane Africaine, Volière...">
            </label>
        </p>
        <p>
            <button type="submit" style="background:green; color:white; padding:10px 15px; cursor:pointer;">Valider le paramétrage</button>
        </p>
    </form>
</body>
</html>