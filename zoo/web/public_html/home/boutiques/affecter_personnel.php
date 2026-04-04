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

// List personnel
$personnel = [];
$stmtP = oci_parse($conn, "SELECT id_personnel, nom_personnel, prenom_personnel, type_personnel FROM Personnel ORDER BY nom_personnel");
oci_execute($stmtP, OCI_DEFAULT);
while ($row = oci_fetch_assoc($stmtP)) { $personnel[] = $row; }
oci_free_statement($stmtP);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_personnel = $_POST['id_personnel'];
    $date_debut = $_POST['date_debut'];
    $date_fin = !empty($_POST['date_fin']) ? $_POST['date_fin'] : null;

    if (empty($id_personnel) || empty($date_debut)) {
        $error = true;
        $message = "Le personnel et la date de début sont obligatoires.";
    } else {
        $sql = "INSERT INTO Personnel_Boutique (id_personnel, id_boutique, date_debut, date_fin) 
                VALUES (:id_p, :id_b, TO_DATE(:dd, 'YYYY-MM-DD'), ";
        
        if ($date_fin) {
            $sql .= "TO_DATE(:df, 'YYYY-MM-DD'))";
        } else {
            $sql .= "NULL)";
        }
        
        $st = oci_parse($conn, $sql);
        oci_bind_by_name($st, ':id_p', $id_personnel);
        oci_bind_by_name($st, ':id_b', $id_boutique);
        oci_bind_by_name($st, ':dd', $date_debut);
        if ($date_fin) {
            oci_bind_by_name($st, ':df', $date_fin);
        }

        if (@oci_execute($st, OCI_COMMIT_ON_SUCCESS)) {
            $message = "Personnel affecté avec succès !";
        } else {
            $e = oci_error($st);
            $error = true;
            $message = "Erreur (Possiblement déjà affecté à cette date) : " . htmlentities($e['message']);
        }
        oci_free_statement($st);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Affecter du Personnel</title>
</head>
<body>
    <a href="view.php?id=<?php echo urlencode($id_boutique); ?>">← Revenir à la vue Boutique</a>
    <h1>Embaucher/Affecter du Personnel (Boutique n°<?php echo htmlspecialchars($id_boutique); ?>)</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <p>
            <label>Employé à affecter :<br>
            <select name="id_personnel" required>
                <option value="">-- Choisir le personnel --</option>
                <?php foreach($personnel as $p): ?>
                    <option value="<?php echo $p['ID_PERSONNEL']; ?>">
                        <?php echo htmlspecialchars($p['NOM_PERSONNEL'] . ' ' . $p['PRENOM_PERSONNEL'] . ' (' . $p['TYPE_PERSONNEL'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            </label>
        </p>
        <p>
            <label>Date de prise de poste :<br>
            <input type="date" name="date_debut" required value="<?php echo date('Y-m-d'); ?>">
            </label>
        </p>
        <p>
            <label>Date de fin d'affectation (Optionnel si en cours) :<br>
            <input type="date" name="date_fin">
            </label>
        </p>
        <p><button type="submit" style="background:green; color:white; padding:10px; cursor:pointer;">Confirmer l'Affectation</button></p>
    </form>
</body>
</html>