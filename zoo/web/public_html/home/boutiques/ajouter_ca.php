<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$id_boutique = $_GET['id'] ?? null;
if (!$id_boutique) {
    header("Location: index.php");
    exit;
}

$message = '';
$error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $montant = $_POST['montant'];
    $date_ca = $_POST['date_ca'];

    if ($montant < 0) {
        $error = true;
        $message = "Le montant ne peut pas être négatif.";
    } else {
        $stmtId = oci_parse($conn, "SELECT NVL(MAX(id_ca), 0) + 1 AS new_id FROM Chiffre_affaire");
        oci_execute($stmtId, OCI_DEFAULT);
        $rowId = oci_fetch_assoc($stmtId);
        $newId = $rowId['NEW_ID'];
        oci_free_statement($stmtId);

        $sql = "INSERT INTO Chiffre_affaire (id_ca, date_ca, montant_ca, id_boutique)
                VALUES (:id, TO_DATE(:date_ca, 'YYYY-MM-DD'), :montant, :id_b)";
        $st = oci_parse($conn, $sql);
        oci_bind_by_name($st, ':id', $newId);
        oci_bind_by_name($st, ':date_ca', $date_ca);
        oci_bind_by_name($st, ':montant', $montant);
        oci_bind_by_name($st, ':id_b', $id_boutique);

        if (@oci_execute($st, OCI_COMMIT_ON_SUCCESS)) {
            $message = "Chiffre d'affaires enregistré !";
        } else {
            $e = oci_error($st);
            $error = true;
            $message = "Erreur : " . htmlentities($e['message']);
        }
        oci_free_statement($st);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisir Chiffre d'Affaires</title>
</head>
<body>
    <a href="view.php?id=<?php echo urlencode($id_boutique); ?>">← Revenir à la boutique</a>
    <h1>Saisir du Chiffre d'Affaires (Boutique n°<?php echo htmlspecialchars($id_boutique); ?>)</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <p>
            <label>Date de recette :<br>
            <input type="date" name="date_ca" value="<?php echo date('Y-m-d'); ?>" required max="<?php echo date('Y-m-d'); ?>">
            </label>
        </p>
        <p>
            <label>Montant réalisé (€) :<br>
            <input type="number" step="0.01" min="0" name="montant" required>
            </label>
        </p>
        <p><button type="submit" style="background:green; color:white; padding:10px; cursor:pointer;">Valider le montant</button></p>
    </form>
</body>
</html>