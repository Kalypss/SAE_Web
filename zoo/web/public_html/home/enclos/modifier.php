<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;
$id_enclo = $_GET['id'] ?? null;

if (!$id_enclo) {
    header("Location: index.php");
    exit;
}

$zones = [];
$stmt = oci_parse($conn, "SELECT id_zone, nom_zone FROM Zone ORDER BY nom_zone");
oci_execute($stmt, OCI_DEFAULT);
while ($row = oci_fetch_assoc($stmt)) { $zones[] = $row; }
oci_free_statement($stmt);

// Récupération
$query = "SELECT id_enclo, id_zone, surface_enclo, latitude_enclo, longitude_enclo 
          FROM Enclos 
          WHERE id_enclo = :id";
$st = oci_parse($conn, $query);
oci_bind_by_name($st, ':id', $id_enclo);
oci_execute($st, OCI_DEFAULT);
$enclo = oci_fetch_assoc($st);
oci_free_statement($st);

if (!$enclo) {
    die("Aucun enclos de ce numéro.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_zone = $_POST['id_zone'];
    $surface = $_POST['surface'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    if (empty($id_zone) || empty($surface) || empty($latitude) || empty($longitude)) {
        $error = true;
        $message = "Veuillez informer les proportions.";
    } else {
        $sql = "UPDATE Enclos SET 
                id_zone = :id_zone, 
                surface_enclo = :surface, 
                latitude_enclo = :latitude, 
                longitude_enclo = :longitude 
                WHERE id_enclo = :id";
        
        $u_st = oci_parse($conn, $sql);
        oci_bind_by_name($u_st, ':id_zone', $id_zone);
        oci_bind_by_name($u_st, ':surface', $surface);
        oci_bind_by_name($u_st, ':latitude', $latitude);
        oci_bind_by_name($u_st, ':longitude', $longitude);
        oci_bind_by_name($u_st, ':id', $id_enclo);

        $r = @oci_execute($u_st, OCI_COMMIT_ON_SUCCESS);
        
        if ($r) {
            $message = "Caractéristiques de l'enclos modifiées !";
            $enclo['ID_ZONE'] = $id_zone;
            $enclo['SURFACE_ENCLO'] = $surface;
            $enclo['LATITUDE_ENCLO'] = $latitude;
            $enclo['LONGITUDE_ENCLO'] = $longitude;
        } else {
            $e = oci_error($u_st);
            $error = true;
            $message = "Erreur de contrainte technique : " . htmlentities($e['message']);
        }
        oci_free_statement($u_st);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier fiche Enclos</title>
</head>
<body>
    <a href="view.php?id=<?php echo urlencode($id_enclo); ?>">← Revenir à la vue détaillée</a>
    <h1>Panneau Modification Enclos : <?php echo htmlspecialchars($enclo['ID_ENCLO']); ?></h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <p>
            <label>Nouvelle attribution de Zone * :<br>
            <select name="id_zone" required>
                <?php foreach($zones as $z): ?>
                    <option value="<?php echo $z['ID_ZONE']; ?>" <?php if($z['ID_ZONE']==$enclo['ID_ZONE']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($z['NOM_ZONE']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            </label>
        </p>

        <p>
            <label>Étirer / Réduire la Surface (m²) * :<br>
            <input type="number" step="0.01" min="1" name="surface" required value="<?php echo htmlspecialchars($enclo['SURFACE_ENCLO']); ?>">
            </label>
        </p>

        <p>
            <label>Recalibrage Latitude * :<br>
            <input type="number" step="0.01" name="latitude" required value="<?php echo htmlspecialchars($enclo['LATITUDE_ENCLO']); ?>">
            </label>
        </p>

        <p>
            <label>Recalibrage Longitude * :<br>
            <input type="number" step="0.01" name="longitude" required value="<?php echo htmlspecialchars($enclo['LONGITUDE_ENCLO']); ?>">
            </label>
        </p>

        <p><button type="submit" style="background:orange; color:black; padding:10px 15px; cursor:pointer;">Enregistrer ce profil Enclos</button></p>
    </form>
</body>
</html>