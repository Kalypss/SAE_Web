<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$zones = [];
$stmtZ = oci_parse($conn, "SELECT id_zone, nom_zone FROM Zone ORDER BY nom_zone");
oci_execute($stmtZ, OCI_DEFAULT);
while ($row = oci_fetch_assoc($stmtZ)) { $zones[] = $row; }
oci_free_statement($stmtZ);

$message = '';
$error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type_boutique = $_POST['type_boutique'];
    $id_zone = $_POST['id_zone'];

    $stmtId = oci_parse($conn, "SELECT NVL(MAX(id_boutique), 0) + 1 AS new_id FROM Boutique");
    oci_execute($stmtId, OCI_DEFAULT);
    $rowId = oci_fetch_assoc($stmtId);
    $newId = $rowId['NEW_ID'];
    oci_free_statement($stmtId);

    $sql = "INSERT INTO Boutique (id_boutique, type_boutique, id_zone) 
            VALUES (:id, :type_b, :id_zone)";
    $st = oci_parse($conn, $sql);
    oci_bind_by_name($st, ':id', $newId);
    oci_bind_by_name($st, ':type_b', $type_boutique);
    oci_bind_by_name($st, ':id_zone', $id_zone);

    if (@oci_execute($st, OCI_COMMIT_ON_SUCCESS)) {
        $message = "Boutique enregistrée avec succès ! (ID : $newId)";
        $error = false;
    } else {
        $e = oci_error($st);
        $message = "Erreur : " . htmlentities($e['message']);
        $error = true;
    }
    oci_free_statement($st);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleajout.php">
    <title>Ajouter Boutique</title>
</head>
<body>
    <a href="index.php">← Retour aux Boutiques</a>
    <h1>Créer une boutique</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <p>
            <label>Type de Boutique (Boutique de souvenirs, Snack, Restaurant...) :<br>
            <input type="text" name="type_boutique" required maxlength="255">
            </label>
        </p>
        <p>
            <label>Zone d'implantation :<br>
            <select name="id_zone" required>
                <option value="">-- Choisir une zone --</option>
                <?php foreach($zones as $z): ?>
                    <option value="<?php echo $z['ID_ZONE']; ?>">
                        <?php echo htmlspecialchars($z['NOM_ZONE']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            </label>
        </p>
        <p><button type="submit" style="background:green; color:white; padding:10px; cursor:pointer;">Ajouter cette Boutique</button></p>
    </form>
</body>
</html>