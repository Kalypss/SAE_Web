<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;
$id_boutique = $_GET['id'] ?? null;

if (!$id_boutique) {
    header("Location: index.php");
    exit;
}

$zones = [];
$stmtZ = oci_parse($conn, "SELECT id_zone, nom_zone FROM Zone ORDER BY nom_zone");
oci_execute($stmtZ, OCI_DEFAULT);
while ($row = oci_fetch_assoc($stmtZ)) { $zones[] = $row; }
oci_free_statement($stmtZ);

// Fetch existing data
$query = "SELECT type_boutique, id_zone FROM Boutique WHERE id_boutique = :id";
$st = oci_parse($conn, $query);
oci_bind_by_name($st, ':id', $id_boutique);
oci_execute($st, OCI_DEFAULT);
$boutique = oci_fetch_assoc($st);
oci_free_statement($st);

if (!$boutique) {
    die("Boutique introuvable.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['type_boutique'];
    $id_zone = $_POST['id_zone'];

    $sql = "UPDATE Boutique SET type_boutique = :type_b, id_zone = :id_zone WHERE id_boutique = :id";
    $u_st = oci_parse($conn, $sql);
    oci_bind_by_name($u_st, ':type_b', $type);
    oci_bind_by_name($u_st, ':id_zone', $id_zone);
    oci_bind_by_name($u_st, ':id', $id_boutique);

    if (@oci_execute($u_st, OCI_COMMIT_ON_SUCCESS)) {
        $message = "Boutique modifiée avec succès.";
        $boutique['TYPE_BOUTIQUE'] = $type;
        $boutique['ID_ZONE'] = $id_zone;
    } else {
        $e = oci_error($u_st);
        $error = true;
        $message = "Erreur : " . htmlentities($e['message']);
    }
    oci_free_statement($u_st);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Boutique</title>
</head>
<body>
    <a href="view.php?id=<?php echo urlencode($id_boutique); ?>">← Revenir à la vue détaillée</a>
    <h1>Modifier Boutique n°<?php echo htmlspecialchars($id_boutique); ?></h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <p>
            <label>Type de Boutique :<br>
            <input type="text" name="type_boutique" required maxlength="255" value="<?php echo htmlspecialchars($boutique['TYPE_BOUTIQUE']); ?>">
            </label>
        </p>
        <p>
            <label>Zone :<br>
            <select name="id_zone" required>
                <?php foreach($zones as $z): ?>
                    <option value="<?php echo $z['ID_ZONE']; ?>" <?php if($z['ID_ZONE']==$boutique['ID_ZONE']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($z['NOM_ZONE']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            </label>
        </p>
        <p><button type="submit" style="background:orange; color:black; padding:10px; cursor:pointer;">Enregistrer les modifications</button></p>
    </form>
</body>
</html>