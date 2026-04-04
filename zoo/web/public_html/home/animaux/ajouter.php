<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;

// Listes déroulantes
$especes = [];
$stmt = oci_parse($conn, "SELECT id_espece, nomu_espece FROM Especes ORDER BY nomu_espece");
oci_execute($stmt, OCI_DEFAULT);
while ($row = oci_fetch_assoc($stmt)) { $especes[] = $row; }
oci_free_statement($stmt);

$enclos = [];
$stmt = oci_parse($conn, "SELECT enc.id_enclo, z.nom_zone FROM Enclos enc JOIN Zone z ON enc.id_zone = z.id_zone ORDER BY enc.id_enclo");
oci_execute($stmt, OCI_DEFAULT);
while ($row = oci_fetch_assoc($stmt)) { $enclos[] = $row; }
oci_free_statement($stmt);

$soignants = [];
$stmt = oci_parse($conn, "SELECT id_personnel, prenom_personnel, nom_personnel FROM Personnel WHERE type_personnel = 'soignant' ORDER BY prenom_personnel");
oci_execute($stmt, OCI_DEFAULT);
while ($row = oci_fetch_assoc($stmt)) { $soignants[] = $row; }
oci_free_statement($stmt);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $poids = $_POST['poids'] ?? 0;
    $regime = strtolower($_POST['regime'] ?? '');
    $rfid = trim($_POST['rfid'] ?? '');
    $espece = $_POST['espece'] ?? '';
    $enclo = $_POST['enclos'] ?? '';
    $soignant = $_POST['soignant'] ?? '';

    if (empty($nom)) $nom = ' '; // Le schema dit "DEFAULT ' '" pour nom_animal
    
    if (empty($dob) || empty($poids) || empty($regime) || empty($rfid) || empty($espece) || empty($enclo) || empty($soignant)) {
        $error = true;
        $message = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Obtenir le nouvel ID (auto-incrément manuel)
        $id_query = "SELECT NVL(MAX(id_animaux), 0) + 1 AS NEW_ID FROM Animaux";
        $id_stmt = oci_parse($conn, $id_query);
        oci_execute($id_stmt, OCI_DEFAULT);
        $row = oci_fetch_assoc($id_stmt);
        $new_id = $row['NEW_ID'];
        oci_free_statement($id_stmt);

        // Insertion
        $sql = "INSERT INTO Animaux (id_animaux, nom_animal, dob_animal, poids_animal, regime_alimentaire_animal, rfid_animal, id_personnel, id_enclo, id_espece) 
                VALUES (:id, :nom, TO_DATE(:dob, 'YYYY-MM-DD'), :poids, :regime, :rfid, :soignant, :enclo, :espece)";
        
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':id', $new_id);
        oci_bind_by_name($stmt, ':nom', $nom);
        oci_bind_by_name($stmt, ':dob', $dob);
        oci_bind_by_name($stmt, ':poids', $poids);
        oci_bind_by_name($stmt, ':regime', $regime);
        oci_bind_by_name($stmt, ':rfid', $rfid);
        oci_bind_by_name($stmt, ':soignant', $soignant);
        oci_bind_by_name($stmt, ':enclo', $enclo);
        oci_bind_by_name($stmt, ':espece', $espece);

        $r = @oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
        if (!$r) {
            $e = oci_error($stmt);
            $error = true;
            if (strpos($e['message'], 'ORA-00001') !== false || strpos($e['message'], 'UNIQUE') !== false) {
                $message = "Erreur : La puce RFID '$rfid' est déjà utilisée par un autre animal.";
            } else {
                $message = "Date erronée ou erreur de base de données : " . htmlentities($e['message']);
            }
        } else {
            // Succès
            header("Location: /public_html/home/animaux/view.php?id=" . urlencode($new_id));
            exit;
        }
        oci_free_statement($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un animal</title>
</head>
<body>
    <a href="/public_html/home/animaux/index.php">← Retour à la liste</a>
    <h1>Ajouter un nouvel animal</h1>

    <?php if (!empty($message)): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight: bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form action="ajouter.php" method="POST">
        <fieldset>
            <legend>Informations de l'animal</legend>

            <p>
                <label>Nom de l'animal (optionnel) :<br>
                <input type="text" name="nom" value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>"></label>
            </p>

            <p>
                <label>Date de naissance * :<br>
                <input type="date" name="dob" value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>" required></label>
            </p>

            <p>
                <label>Poids en kg (ex: 45.5) * :<br>
                <input type="number" step="0.01" min="0.1" name="poids" value="<?php echo htmlspecialchars($_POST['poids'] ?? ''); ?>" required></label>
            </p>

            <p>
                <label>Régime alimentaire * :<br>
                <select name="regime" required>
                    <option value="">Sélectionnez...</option>
                    <option value="vegetarien" <?php if(($_POST['regime']??'')=='vegetarien') echo 'selected'; ?>>Végétarien</option>
                    <option value="carnivore" <?php if(($_POST['regime']??'')=='carnivore') echo 'selected'; ?>>Carnivore</option>
                    <option value="insectivore" <?php if(($_POST['regime']??'')=='insectivore') echo 'selected'; ?>>Insectivore</option>
                    <option value="filtreur" <?php if(($_POST['regime']??'')=='filtreur') echo 'selected'; ?>>Filtreur</option>
                    <option value="omnivore" <?php if(($_POST['regime']??'')=='omnivore') echo 'selected'; ?>>Omnivore</option>
                </select>
                </label>
            </p>

            <p>
                <label>Code RFID * (doit être unique) :<br>
                <input type="text" name="rfid" style="font-family: monospace;" value="<?php echo htmlspecialchars($_POST['rfid'] ?? ''); ?>" required></label>
            </p>
        </fieldset>

        <fieldset>
            <legend>Affectations</legend>

            <p>
                <label>Espèce * :<br>
                <select name="espece" required>
                    <option value="">Sélectionnez une espèce...</option>
                    <?php foreach($especes as $e): ?>
                        <option value="<?php echo $e['ID_ESPECE']; ?>" <?php if(($_POST['espece']??'')==$e['ID_ESPECE']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($e['NOMU_ESPECE']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </label>
            </p>

            <p>
                <label>Enclos (Emplacement) * :<br>
                <select name="enclos" required>
                    <option value="">Sélectionnez un enclos...</option>
                    <?php foreach($enclos as $enc): ?>
                        <option value="<?php echo $enc['ID_ENCLO']; ?>" <?php if(($_POST['enclos']??'')==$enc['ID_ENCLO']) echo 'selected'; ?>>
                            Enclos n°<?php echo $enc['ID_ENCLO']; ?> (<?php echo htmlspecialchars($enc['NOM_ZONE']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                </label>
            </p>

            <p>
                <label>Soignant Référent * :<br>
                <select name="soignant" required>
                    <option value="">Sélectionnez un soignant...</option>
                    <?php foreach($soignants as $soi): ?>
                        <option value="<?php echo $soi['ID_PERSONNEL']; ?>" <?php if(($_POST['soignant']??'')==$soi['ID_PERSONNEL']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($soi['PRENOM_PERSONNEL'].' '.$soi['NOM_PERSONNEL']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </label>
            </p>

        </fieldset>

        <p><button type="submit">Ajouter l'animal</button></p>
    </form>
</body>
</html>