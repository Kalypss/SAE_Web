<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;

// Listes de sélection
$animaux = [];
$stmt = oci_parse($conn, "SELECT id_animaux, nom_animal FROM Animaux ORDER BY nom_animal");
oci_execute($stmt, OCI_DEFAULT);
while ($r = oci_fetch_assoc($stmt)) { $animaux[] = $r; }
oci_free_statement($stmt);

$personnel = [];
$stmt = oci_parse($conn, "SELECT id_personnel, prenom_personnel, nom_personnel, type_personnel FROM Personnel WHERE type_personnel IN ('veterinaire', 'soignant') ORDER BY nom_personnel");
oci_execute($stmt, OCI_DEFAULT);
while ($r = oci_fetch_assoc($stmt)) { $personnel[] = $r; }
oci_free_statement($stmt);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $animal_id = $_POST['animal_id'];
    $personnel_id = $_POST['personnel_id'];
    $date_soin = $_POST['date_soin'];
    $type_soin = $_POST['type_soin'];
    $description = $_POST['description'];

    if (empty($animal_id) || empty($personnel_id) || empty($date_soin) || empty($type_soin)) {
        $error = true;
        $message = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $sql_id = "SELECT NVL(MAX(id_soin), 0) + 1 AS next_id FROM Soins";
        $stmt_id = oci_parse($conn, $sql_id);
        oci_execute($stmt_id, OCI_DEFAULT);
        $row_id = oci_fetch_assoc($stmt_id);
        $next_id = $row_id['NEXT_ID'];
        oci_free_statement($stmt_id);

        $sql = "INSERT INTO Soins (id_soin, id_animaux, id_personnel, date_soin, type_soin, description_soin) 
                VALUES (:id_soin, :id_animaux, :id_personnel, TO_DATE(:date_soin, 'YYYY-MM-DD'), :type_soin, :description)";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':id_soin', $next_id);
        oci_bind_by_name($stmt, ':id_animaux', $animal_id);
        oci_bind_by_name($stmt, ':id_personnel', $personnel_id);
        oci_bind_by_name($stmt, ':date_soin', $date_soin);
        oci_bind_by_name($stmt, ':type_soin', $type_soin);
        oci_bind_by_name($stmt, ':description', $description);

        $r = @oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
        if ($r) {
            $message = "Soin ajouté avec succès ! Redirection...";
            header("refresh:2;url=view.php?id=" . $next_id);
        } else {
            $e = oci_error($stmt);
            $error = true;
            $message = "Erreur lors de l'ajout : " . htmlentities($e['message']);
        }
        oci_free_statement($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleajout.css">
    <title>Ajouter un soin</title>
</head>
<body>
    <a href="index.php">← Retour aux Soins</a>
    <h1>Enregistrer une intervention de soin</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <div class="formul">
        <p>
            <label>Patient (Animal) *:<br>
            <select name="animal_id" required>
                <option value="">-- Sélectionner l'animal --</option>
                <?php foreach($animaux as $a): ?>
                    <option value="<?php echo $a['ID_ANIMAUX']; ?>"><?php echo htmlspecialchars($a['NOM_ANIMAL'].' (#'.$a['ID_ANIMAUX'].')'); ?></option>
                <?php endforeach; ?>
            </select>
            </label>
        </p>

        <p>
            <label>Intervenant *:<br>
            <select name="personnel_id" required>
                <option value="">-- Sélectionner l'intervenant --</option>
                <?php foreach($personnel as $p): ?>
                    <option value="<?php echo $p['ID_PERSONNEL']; ?>">
                        <?php echo htmlspecialchars($p['PRENOM_PERSONNEL'].' '.$p['NOM_PERSONNEL'].' ('.$p['TYPE_PERSONNEL'].')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            </label>
        </p>

        <p>
            <label>Date du soin * (ex: AAAA-MM-JJ):<br>
            <input type="date" name="date_soin" required value="<?php echo date('Y-m-d'); ?>">
            </label>
        </p>

        <p>
            <label>Type de soin *:<br>
            <select name="type_soin" required>
                <option value="Vaccination">Vaccination</option>
                <option value="Controle">Controle (Check-up / Visite de routine)</option>
                <option value="Traitement">Traitement (Médicament, pansement...)</option>
                <option value="Chirurgie">Chirurgie (Opération)</option>
            </select>
            </label>
        </p>

        <p>
            <label>Description / Compte rendu du soin :<br>
            <textarea name="description" rows="5" cols="50" placeholder="Observations, diagnostic, posologie..."></textarea>
            </label>
        </p>
        </div>

        <p class="bouton"><button type="submit">Enregistrer ce soin</button></p>
    </form>
</body>
</html>