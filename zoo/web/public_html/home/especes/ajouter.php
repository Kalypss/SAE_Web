<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomU = trim($_POST['nomU']);
    $nomL = trim($_POST['nomL']);
    $est_menace = isset($_POST['est_menace']) ? 1 : 0;

    if (empty($nomU) || empty($nomL)) {
        $error = true;
        $message = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $sql_id = "SELECT NVL(MAX(id_espece), 0) + 1 AS next_id FROM Especes";
        $stmt_id = oci_parse($conn, $sql_id);
        oci_execute($stmt_id, OCI_DEFAULT);
        $row_id = oci_fetch_assoc($stmt_id);
        $next_id = $row_id['NEXT_ID'];
        oci_free_statement($stmt_id);

        $sql = "INSERT INTO Especes (id_espece, nomU_espece, nomL_espece, est_menace) 
                VALUES (:id_espece, :nomU, :nomL, :est_menace)";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':id_espece', $next_id);
        oci_bind_by_name($stmt, ':nomU', $nomU);
        oci_bind_by_name($stmt, ':nomL', $nomL);
        oci_bind_by_name($stmt, ':est_menace', $est_menace);

        $r = @oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
        if ($r) {
            $message = "Nouvelle espèce enregistrée avec succès ! Redirection...";
            header("refresh:2;url=view.php?id=" . $next_id);
        } else {
            $e = oci_error($stmt);
            $error = true;
            if (strpos($e['message'], 'UNIQUE') !== false) {
                $message = "Erreur : Le nom latin '{$nomL}' existe déjà dans le registre.";
            } else {
                $message = "Erreur lors de l'ajout : " . htmlentities($e['message']);
            }
        }
        oci_free_statement($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une espèce</title>
</head>
<body>
    <a href="index.php">← Retour à la liste des espèces</a>
    <h1>Déclarer / Créer une Nouvelle Espèce</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <p>
            <label>Nom Usuel / Commun * (ex. Lion d'Afrique) :<br>
            <input type="text" name="nomU" required placeholder="Nom couramment utilisé" size="40">
            </label>
        </p>

        <p>
            <label>Nom Scientifique (Latin) * (doit être unique) :<br>
            <input type="text" name="nomL" required placeholder="Ex: Panthera leo" size="40" style="font-style:italic;">
            </label>
        </p>

        <p>
            <label>
            <input type="checkbox" name="est_menace" value="1">
            <strong>Cocher si espèce menacée ou nécessitant des mesures de préservation spécifiques.</strong>
            </label>
        </p>

        <p><button type="submit" style="background:#4CAF50; color:white; padding:10px 15px; cursor:pointer;">Enregistrer l'espèce</button></p>
    </form>
</body>
</html>