<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;

// Listes pour le Select
$prestataires = [];
$stmt = oci_parse($conn, "SELECT id_prestataire, nom_prestataire FROM Prestataire ORDER BY nom_prestataire");
oci_execute($stmt, OCI_DEFAULT);
while ($r = oci_fetch_assoc($stmt)) { $prestataires[] = $r; }
oci_free_statement($stmt);

$enclos = [];
$stmt = oci_parse($conn, "SELECT e.id_enclo, z.nom_zone FROM Enclos e JOIN Zone z ON e.id_zone = z.id_zone ORDER BY e.id_enclo");
oci_execute($stmt, OCI_DEFAULT);
while ($r = oci_fetch_assoc($stmt)) { $enclos[] = $r; }
oci_free_statement($stmt);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nature = $_POST['nature'];
    $date_rep = $_POST['date_rep'];
    $id_presta = $_POST['id_prestataire'];
    $id_enclo = $_POST['id_enclo'];
    $id_personnel = $_SESSION['user_id']; // Celui qui déclare

    if (empty($nature) || empty($date_rep) || empty($id_presta) || empty($id_enclo)) {
        $error = true;
        $message = "Veuillez remplir tous les champs.";
    } else {
        $sql_id = "SELECT NVL(MAX(id_reparation), 0) + 1 AS next_id FROM Reparation";
        $stmt_id = oci_parse($conn, $sql_id);
        oci_execute($stmt_id, OCI_DEFAULT);
        $row_id = oci_fetch_assoc($stmt_id);
        $next_id = $row_id['NEXT_ID'];
        oci_free_statement($stmt_id);

        $sql = "INSERT INTO Reparation (id_reparation, nature_reparation, date_reparation, id_prestataire, id_personnel, id_enclo) 
                VALUES (:id_rep, :nature, TO_DATE(:date_rep, 'YYYY-MM-DD'), :id_presta, :id_pers, :id_enclo)";
        $st = oci_parse($conn, $sql);
        oci_bind_by_name($st, ':id_rep', $next_id);
        oci_bind_by_name($st, ':nature', $nature);
        oci_bind_by_name($st, ':date_rep', $date_rep);
        oci_bind_by_name($st, ':id_presta', $id_presta);
        oci_bind_by_name($st, ':id_pers', $id_personnel);
        oci_bind_by_name($st, ':id_enclo', $id_enclo);

        $r = @oci_execute($st, OCI_COMMIT_ON_SUCCESS);
        if ($r) {
            $message = "Intervention planifiée ou enregistrée avec succès !";
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
    <link rel="stylesheet" href="../../styleajout.css">
    <title>Ajouter Réparation</title>
</head>
<body>
    <a href="index.php">← Retour au registre des réparations</a>
    <h1>Déclarer une Réparation</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <div class="formul">
        <p>
            <label>Date de l'intervention * :<br>
            <input type="date" name="date_rep" required value="<?php echo date('Y-m-d'); ?>">
            </label>
        </p>

        <p>
            <label>Prestataire externe chargé des travaux * :<br>
            <select name="id_prestataire" required>
                <option value="">-- Choisir un partenaire --</option>
                <?php foreach($prestataires as $p): ?>
                    <option value="<?php echo $p['ID_PRESTATAIRE']; ?>"><?php echo htmlspecialchars($p['NOM_PRESTATAIRE']); ?></option>
                <?php endforeach; ?>
            </select>
            </label>
            <br><i><small>Si le prestataire n'existe pas, <a href="../prestataires/ajouter.php">ajoutez le ici d'abord</a>.</small></i>
        </p>

        <p>
            <label>Enclos concerné * :<br>
            <select name="id_enclo" required>
                <option value="">-- Choisir un enclos --</option>
                <?php foreach($enclos as $e): ?>
                    <option value="<?php echo $e['ID_ENCLO']; ?>">Enclos n°<?php echo htmlspecialchars($e['ID_ENCLO'] . ' (' . $e['NOM_ZONE'] . ')'); ?></option>
                <?php endforeach; ?>
            </select>
            </label>
        </p>

        <p>
            <label>Nature des travaux * :<br>
            <textarea name="nature" required rows="4" cols="50" placeholder="Changement clôture, Peinture..."></textarea>
            </label>
        </p>
        </div>

        <p class="bouton"><button type="submit">Enregistrer l'intervention</button></p>
    </form>
</body>
</html>