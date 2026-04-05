<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

// Prepare selects
$animaux = [];
$stmtA = oci_parse($conn, "SELECT id_animaux, nom_animal FROM Animaux ORDER BY nom_animal");
oci_execute($stmtA, OCI_DEFAULT);
while ($row = oci_fetch_assoc($stmtA)) { $animaux[] = $row; }
oci_free_statement($stmtA);

$personnel = [];
// Pour l'alimentation, on prend en compte tout le personnel, ou vous pourriez filtrer `WHERE type_personnel = 'soigneur'` par ex.
$stmtP = oci_parse($conn, "SELECT id_personnel, nom_personnel, prenom_personnel, type_personnel FROM Personnel ORDER BY nom_personnel");
oci_execute($stmtP, OCI_DEFAULT);
while ($row = oci_fetch_assoc($stmtP)) { $personnel[] = $row; }
oci_free_statement($stmtP);

$message = '';
$error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_animaux = $_POST['id_animaux'];
    $id_personnel = $_POST['id_personnel'];
    $dose = $_POST['dose'];
    $date = date('Y-m-d'); // Enregistré à la date d'aujourd'hui

    // Calcul Maxi ID
    $stmtId = oci_parse($conn, "SELECT NVL(MAX(id_alimentation), 0) + 1 AS new_id FROM Alimentation");
    oci_execute($stmtId, OCI_DEFAULT);
    $rowId = oci_fetch_assoc($stmtId);
    $newId = $rowId['NEW_ID'];
    oci_free_statement($stmtId);

    $sql = "INSERT INTO Alimentation (id_alimentation, id_animaux, id_personnel, dose_journaliere_alimentation, date_alimentation)
            VALUES (:id, :id_animaux, :id_personnel, :dose, TO_DATE(:date_str, 'YYYY-MM-DD'))";
    
    $ins = oci_parse($conn, $sql);
    oci_bind_by_name($ins, ':id', $newId);
    oci_bind_by_name($ins, ':id_animaux', $id_animaux);
    oci_bind_by_name($ins, ':id_personnel', $id_personnel);
    oci_bind_by_name($ins, ':dose', $dose);
    oci_bind_by_name($ins, ':date_str', $date);

    $r = @oci_execute($ins, OCI_COMMIT_ON_SUCCESS);
    if ($r) {
        $message = "Alimentation ajoutée avec succès ! (ID : $newId)";
        $error = false;
    } else {
        $e = oci_error($ins);
        $message = "Erreur lors de l'ajout : " . htmlentities($e['message']);
        $error = true;
    }
    oci_free_statement($ins);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleajout.css">
    <title>Ajouter Alimentation</title>
</head>
<body>
    <a href="index.php">← Retour à la liste</a>
    <h1>Saisir une distribution alimentaire</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <div class="formul">
        <p>
            <label>Saisisseur / Personnel :<br>
            <select name="id_personnel" required>
                <?php foreach($personnel as $p): ?>
                    <option value="<?php echo $p['ID_PERSONNEL']; ?>">
                        <?php echo htmlspecialchars($p['NOM_PERSONNEL'] . ' ' . $p['PRENOM_PERSONNEL'] . ' (' . $p['TYPE_PERSONNEL'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            </label>
        </p>
        <p>
            <label>Animal à nourrir :<br>
            <select name="id_animaux" required>
                <?php foreach($animaux as $a): ?>
                    <option value="<?php echo $a['ID_ANIMAUX']; ?>">
                        <?php echo htmlspecialchars($a['NOM_ANIMAL']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            </label>
        </p>
        <p>
            <label>Dose journalière distribuée :<br>
            <input type="number" step="0.01" min="0.01" name="dose" required>
            </label>
        </p>

        </div>

        <p class="bouton">
            <button type="submit">Confirmer l'alimentation</button>
        </p>
    </form>
</body>
</html>