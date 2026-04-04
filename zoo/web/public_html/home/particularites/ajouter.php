<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom_particularite']);

    if (empty($nom)) {
        $error = true;
        $message = "Veuillez renseigner la dénomination de particularité.";
    } else {
        $stmtId = oci_parse($conn, "SELECT NVL(MAX(id_particularite), 0) + 1 AS new_id FROM Particularite");
        oci_execute($stmtId, OCI_DEFAULT);
        $rowId = oci_fetch_assoc($stmtId);
        $newId = $rowId['NEW_ID'];
        oci_free_statement($stmtId);

        $sql = "INSERT INTO Particularite (id_particularite, nom_particularite) VALUES (:id, :nom)";
        $ins = oci_parse($conn, $sql);
        oci_bind_by_name($ins, ':id', $newId);
        oci_bind_by_name($ins, ':nom', $nom);

        $r = @oci_execute($ins, OCI_COMMIT_ON_SUCCESS);
        if ($r) {
            $message = "Particularité référencée ! (ID : $newId)";
            $error = false;
        } else {
            $e = oci_error($ins);
            $message = "Erreur (Possiblement un doublon) : " . htmlentities($e['message']);
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
    <title>Ajouter Particularité</title>
</head>
<body>
    <a href="index.php">← Retour au lexique</a>
    <h1>Définir un Nouveau Trait (Particularité Animale)</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <p>
            <label>Intitulé unique de la condition / caractéristique :<br>
            <input type="text" name="nom_particularite" required maxlength="250" placeholder="Agressif, Albinos, Hybride, Rare...">
            </label>
        </p>
        <p>
            <button type="submit" style="background:green; color:white; padding:10px 15px; cursor:pointer;">Injecter dans le système</button>
        </p>
    </form>
</body>
</html>