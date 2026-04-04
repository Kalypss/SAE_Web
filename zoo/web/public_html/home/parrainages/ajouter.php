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

$visiteurs = [];
$stmtV = oci_parse($conn, "SELECT id_visiteur, nom_visiteur, prenom_visiteur FROM Visiteur ORDER BY nom_visiteur");
oci_execute($stmtV, OCI_DEFAULT);
while ($row = oci_fetch_assoc($stmtV)) { $visiteurs[] = $row; }
oci_free_statement($stmtV);

$message = '';
$error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_visiteur = $_POST['id_visiteur'];
    $id_animaux = $_POST['id_animaux'];
    $niveau = $_POST['niveau'];
    $contribution = $_POST['contribution'];
    $date = date('Y-m-d'); // Enregistré à la date d'aujourd'hui

    if (empty($id_visiteur) || empty($id_animaux) || empty($niveau) || $contribution < 0) {
        $error = true;
        $message = "Données invalides : Le don doit être >= 0 et les listes sélectionnées.";
    } else {
        $stmtId = oci_parse($conn, "SELECT NVL(MAX(id_parrainage), 0) + 1 AS new_id FROM Parrainage");
        oci_execute($stmtId, OCI_DEFAULT);
        $rowId = oci_fetch_assoc($stmtId);
        $newId = $rowId['NEW_ID'];
        oci_free_statement($stmtId);

        $sql = "INSERT INTO Parrainage (id_parrainage, id_visiteur, id_animaux, niveau, contribution, date_debut)
                VALUES (:id, :id_v, :id_a, :niv, :contrib, TO_DATE(:date_str, 'YYYY-MM-DD'))";
        
        $ins = oci_parse($conn, $sql);
        oci_bind_by_name($ins, ':id', $newId);
        oci_bind_by_name($ins, ':id_v', $id_visiteur);
        oci_bind_by_name($ins, ':id_a', $id_animaux);
        oci_bind_by_name($ins, ':niv', $niveau);
        oci_bind_by_name($ins, ':contrib', $contribution);
        oci_bind_by_name($ins, ':date_str', $date);

        $r = @oci_execute($ins, OCI_COMMIT_ON_SUCCESS);
        if ($r) {
            $message = "Parrainage enregistré avec la générosité attendue ! (ID : $newId)";
            $error = false;
        } else {
            $e = oci_error($ins);
            $message = "Erreur lors de l'ajout : " . htmlentities($e['message']);
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
    <title>Ajouter un nouveau Parrainage</title>
</head>
<body>
    <a href="index.php">← Retour à la liste des Parrains</a>
    <h1>Créer / Enregistrer un Parrainage Animalier</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <p>
            <label>Visiteur Donateur :<br>
            <select name="id_visiteur" required>
                <option value="">-- Sélectionner un Visiteur Enregistré --</option>
                <?php foreach($visiteurs as $v): ?>
                    <option value="<?php echo $v['ID_VISITEUR']; ?>">
                        <?php echo htmlspecialchars($v['NOM_VISITEUR'] . ' ' . $v['PRENOM_VISITEUR']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            </label>
        </p>

        <p>
            <label>Animal Sponsorisé :<br>
            <select name="id_animaux" required>
                <option value="">-- Sélectionner l'Animal Parrainé --</option>
                <?php foreach($animaux as $a): ?>
                    <option value="<?php echo $a['ID_ANIMAUX']; ?>">
                        <?php echo htmlspecialchars($a['NOM_ANIMAL'] . ' (ID '. $a['ID_ANIMAUX'] .')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            </label>
        </p>

        <p>
            <label>Pack d'Engagement (Niveau) :<br>
            <select name="niveau" required>
                <option value="rien">Rien / Par défaut</option>
                <option value="bronze">Bronze</option>
                <option value="argent">Argent</option>
                <option value="or">Or</option>
            </select>
            </label>
        </p>

        <p>
            <label>Saisir la contribution libre d'entrée (€) :<br>
            <input type="number" step="0.01" min="0" name="contribution" required>
            </label>
        </p>

        <p>
            <button type="submit" style="background:green; color:white; padding:10px 15px; cursor:pointer;">Facturer le Parrainage</button>
        </p>
    </form>
</body>
</html>