<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;
$id_animal = $_GET['id'] ?? null;

if (!$id_animal) {
    header("Location: /public_html/home/animaux/index.php");
    exit;
}

// Fetch animal data
$q_animal = "SELECT id_animaux, nom_animal, TO_CHAR(dob_animal, 'YYYY-MM-DD') as dob_animal_input, poids_animal, regime_alimentaire_animal, rfid_animal, id_personnel, id_enclo, id_espece FROM Animaux WHERE id_animaux = :id";
$st = oci_parse($conn, $q_animal);
oci_bind_by_name($st, ':id', $id_animal);
oci_execute($st, OCI_DEFAULT);
$animal = oci_fetch_assoc($st);
oci_free_statement($st);

if (!$animal) {
    die("Animal introuvable.");
}

// Listes pour le formulaire
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

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $poids = $_POST['poids'] ?? 0;
    $regime = strtolower($_POST['regime'] ?? '');
    $rfid = trim($_POST['rfid'] ?? '');
    $espece = $_POST['espece'] ?? '';
    $enclo = $_POST['enclos'] ?? '';
    $soignant = $_POST['soignant'] ?? '';

    if (empty($dob) || empty($poids) || empty($regime) || empty($rfid) || empty($espece) || empty($enclo) || empty($soignant)) {
        $error = true;
        $message = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $sql = "UPDATE Animaux SET 
                nom_animal = :nom, 
                dob_animal = TO_DATE(:dob, 'YYYY-MM-DD'), 
                poids_animal = :poids, 
                regime_alimentaire_animal = :regime, 
                rfid_animal = :rfid, 
                id_personnel = :soignant, 
                id_enclo = :enclo, 
                id_espece = :espece 
                WHERE id_animaux = :id";
        
        $u_stmt = oci_parse($conn, $sql);
        oci_bind_by_name($u_stmt, ':nom', $nom);
        oci_bind_by_name($u_stmt, ':dob', $dob);
        oci_bind_by_name($u_stmt, ':poids', $poids);
        oci_bind_by_name($u_stmt, ':regime', $regime);
        oci_bind_by_name($u_stmt, ':rfid', $rfid);
        oci_bind_by_name($u_stmt, ':soignant', $soignant);
        oci_bind_by_name($u_stmt, ':enclo', $enclo);
        oci_bind_by_name($u_stmt, ':espece', $espece);
        oci_bind_by_name($u_stmt, ':id', $id_animal);

        $r = @oci_execute($u_stmt, OCI_COMMIT_ON_SUCCESS);
        if (!$r) {
            $e = oci_error($u_stmt);
            $error = true;
            if (strpos($e['message'], 'UNIQUE') !== false) {
                $message = "Erreur : La puce RFID '$rfid' est déjà utilisée.";
            } else {
                $message = "Date erronée ou erreur de base de données : " . htmlentities($e['message']);
            }
        } else {
            // Update le tableau local pour l'affichage directement
            $animal['NOM_ANIMAL'] = $nom;
            $animal['DOB_ANIMAL_INPUT'] = $dob;
            $animal['POIDS_ANIMAL'] = $poids;
            $animal['REGIME_ALIMENTAIRE_ANIMAL'] = $regime;
            $animal['RFID_ANIMAL'] = $rfid;
            $animal['ID_ESPECE'] = $espece;
            $animal['ID_ENCLO'] = $enclo;
            $animal['ID_PERSONNEL'] = $soignant;

            $error = false;
            $message = "Modification enregistrée avec succès !";
            // Normalement on redirige mais un message c'est bien aussi
        }
        oci_free_statement($u_stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un animal</title>
</head>
<body>
    <a href="/public_html/home/animaux/view.php?id=<?php echo urlencode($id_animal); ?>">← Retour à la Fiche de <?php echo htmlspecialchars($animal['NOM_ANIMAL'] ?? 'l\'animal'); ?></a>
    <h1>Modifier l'animal #<?php echo htmlspecialchars($id_animal); ?></h1>

    <?php if (!empty($message)): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight: bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form action="modifier.php?id=<?php echo urlencode($id_animal); ?>" method="POST">
        <fieldset>
            <legend>Informations de l'animal</legend>

            <p>
                <label>Nom de l'animal :<br>
                <input type="text" name="nom" value="<?php echo htmlspecialchars(trim($animal['NOM_ANIMAL']) ?? ''); ?>"></label>
            </p>

            <p>
                <label>Date de naissance * :<br>
                <input type="date" name="dob" value="<?php echo htmlspecialchars($animal['DOB_ANIMAL_INPUT'] ?? ''); ?>" required></label>
            </p>

            <p>
                <label>Poids en kg * :<br>
                <input type="number" step="0.01" min="0.1" name="poids" value="<?php echo htmlspecialchars($animal['POIDS_ANIMAL'] ?? ''); ?>" required></label>
            </p>

            <p>
                <label>Régime alimentaire * :<br>
                <select name="regime" required>
                    <?php $reg = strtolower(trim($animal['REGIME_ALIMENTAIRE_ANIMAL'] ?? '')); ?>
                    <option value="vegetarien" <?php if($reg=='vegetarien') echo 'selected'; ?>>Végétarien</option>
                    <option value="carnivore" <?php if($reg=='carnivore') echo 'selected'; ?>>Carnivore</option>
                    <option value="insectivore" <?php if($reg=='insectivore') echo 'selected'; ?>>Insectivore</option>
                    <option value="filtreur" <?php if($reg=='filtreur') echo 'selected'; ?>>Filtreur</option>
                    <option value="omnivore" <?php if($reg=='omnivore') echo 'selected'; ?>>Omnivore</option>
                </select>
                </label>
            </p>

            <p>
                <label>Code RFID * (doit être unique) :<br>
                <input type="text" name="rfid" style="font-family: monospace;" value="<?php echo htmlspecialchars($animal['RFID_ANIMAL'] ?? ''); ?>" required></label>
            </p>
        </fieldset>

        <fieldset>
            <legend>Affectations</legend>

            <p>
                <label>Espèce * :<br>
                <select name="espece" required>
                    <?php foreach($especes as $e): ?>
                        <option value="<?php echo $e['ID_ESPECE']; ?>" <?php if($animal['ID_ESPECE']==$e['ID_ESPECE']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($e['NOMU_ESPECE']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </label>
            </p>

            <p>
                <label>Enclos (Emplacement) * :<br>
                <select name="enclos" required>
                    <?php foreach($enclos as $enc): ?>
                        <option value="<?php echo $enc['ID_ENCLO']; ?>" <?php if($animal['ID_ENCLO']==$enc['ID_ENCLO']) echo 'selected'; ?>>
                            Enclos n°<?php echo $enc['ID_ENCLO']; ?> (<?php echo htmlspecialchars($enc['NOM_ZONE']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                </label>
            </p>

            <p>
                <label>Soignant Référent * :<br>
                <select name="soignant" required>
                    <?php foreach($soignants as $soi): ?>
                        <option value="<?php echo $soi['ID_PERSONNEL']; ?>" <?php if($animal['ID_PERSONNEL']==$soi['ID_PERSONNEL']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($soi['PRENOM_PERSONNEL'].' '.$soi['NOM_PERSONNEL']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </label>
            </p>

        </fieldset>

        <p><button type="submit">Enregistrer les modifications</button></p>
    </form>
</body>
</html>