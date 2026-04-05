<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom_visiteur'];
    $prenom = $_POST['prenom_visiteur'];
    $email = $_POST['email_visiteur'];

    if (empty($nom) || empty($prenom) || empty($email)) {
        $error = true;
        $message = "Tous les champs sont requis.";
    } else {
        $stmtId = oci_parse($conn, "SELECT NVL(MAX(id_visiteur), 0) + 1 AS new_id FROM Visiteur");
        oci_execute($stmtId, OCI_DEFAULT);
        $rowId = oci_fetch_assoc($stmtId);
        $newId = $rowId['NEW_ID'];
        oci_free_statement($stmtId);

        $sql = "INSERT INTO Visiteur (id_visiteur, nom_visiteur, prenom_visiteur, Email_visiteur)
                VALUES (:id, :nom, :prenom, :email)";
        
        $ins = oci_parse($conn, $sql);
        oci_bind_by_name($ins, ':id', $newId);
        oci_bind_by_name($ins, ':nom', $nom);
        oci_bind_by_name($ins, ':prenom', $prenom);
        oci_bind_by_name($ins, ':email', $email);

        $r = @oci_execute($ins, OCI_COMMIT_ON_SUCCESS);
        if ($r) {
            $message = "Visiteur ajouté avec succès ! (ID : $newId)";
            $error = false;
        } else {
            $e = oci_error($ins);
            $message = "Erreur (Possiblement un doublon d'email) : " . htmlentities($e['message']);
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
    <link rel="stylesheet" href="../../styleajout.css">
    <title>Inscrire un Visiteur</title>
</head>
<body>
    <a href="index.php">← Retour à la liste client</a>
    <h1>Inscrire un Nouveau Visiteur</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <div class="formul">
        <p>
            <label>Nom :<br>
            <input type="text" name="nom_visiteur" required maxlength="255">
            </label>
        </p>

        <p>
            <label>Prénom :<br>
            <input type="text" name="prenom_visiteur" required maxlength="255">
            </label>
        </p>

        <p>
            <label>Adresse E-mail :<br>
            <input type="email" name="email_visiteur" required maxlength="255" placeholder="contact@email.com">
            </label>
        </p>
        </div>

        <p class="bouton">
            <button type="submit">Enregistrer ce Client</button>
        </p>
    </form>
</body>
</html>