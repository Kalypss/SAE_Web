<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;

// Récupérer les zones pour la liste déroulante
$zones = [];
$stmt = oci_parse($conn, "SELECT id_zone, nom_zone FROM Zone ORDER BY nom_zone");
oci_execute($stmt, OCI_DEFAULT);
while ($row = oci_fetch_assoc($stmt)) {
    $zones[] = $row;
}
oci_free_statement($stmt);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_zone = $_POST['id_zone'];
    $surface = $_POST['surface'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    if (empty($id_zone) || empty($surface) || empty($latitude) || empty($longitude)) {
        $error = true;
        $message = "Veuillez remplir tous les champs.";
    } else {
        $sql_id = "SELECT NVL(MAX(id_enclo), 0) + 1 AS next_id FROM Enclos";
        $stmt_id = oci_parse($conn, $sql_id);
        oci_execute($stmt_id, OCI_DEFAULT);
        $row_id = oci_fetch_assoc($stmt_id);
        $next_id = $row_id['NEXT_ID'];
        oci_free_statement($stmt_id);

        $sql = "INSERT INTO Enclos (id_enclo, id_zone, surface_enclo, latitude_enclo, longitude_enclo) 
                VALUES (:id_enclo, :id_zone, :surface, :latitude, :longitude)";
        $st = oci_parse($conn, $sql);
        oci_bind_by_name($st, ':id_enclo', $next_id);
        oci_bind_by_name($st, ':id_zone', $id_zone);
        oci_bind_by_name($st, ':surface', $surface);
        oci_bind_by_name($st, ':latitude', $latitude);
        oci_bind_by_name($st, ':longitude', $longitude);

        $r = @oci_execute($st, OCI_COMMIT_ON_SUCCESS);
        if ($r) {
            $message = "Enclos créé avec succès. Redirection...";
            header("refresh:2;url=view.php?id=" . $next_id);
        } else {
            $e = oci_error($st);
            $error = true;
            $message = "Erreur lors de la création : " . htmlentities($e['message']);
        }
        oci_free_statement($st);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un enclos</title>
</head>
<body>
    <a href="index.php">← Retour à la liste des enclos</a>
    <h1>Créer un nouvel enclos</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <p>
            <label>Zone d'appartenance * :<br>
            <select name="id_zone" required>
                <option value="">-- Choisir une zone --</option>
                <?php foreach($zones as $z): ?>
                    <option value="<?php echo $z['ID_ZONE']; ?>"><?php echo htmlspecialchars($z['NOM_ZONE']); ?></option>
                <?php endforeach; ?>
            </select>
            </label>
        </p>

        <p>
            <label>Surface (m²) * :<br>
            <input type="number" step="0.01" min="1" name="surface" required>
            </label>
        </p>

        <p>
            <label>Latitude * :<br>
            <input type="number" step="0.01" name="latitude" required placeholder="Ex: 45.12">
            </label>
        </p>

        <p>
            <label>Longitude * :<br>
            <input type="number" step="0.01" name="longitude" required placeholder="Ex: 1.23">
            </label>
        </p>

        <p><button type="submit" style="background:#4CAF50; color:white; padding:10px 15px; cursor:pointer;">Enregistrer l'enclos</button></p>
    </form>
</body>
</html>