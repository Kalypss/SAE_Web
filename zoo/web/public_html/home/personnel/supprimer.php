<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$id_pers = $_GET['id'] ?? null;

if (!$id_pers) {
    header("Location: index.php");
    exit;
}

// Empêcher l'utilisateur de se supprimer lui-même
if ($id_pers == $_SESSION['user_id']) {
    die("<div style='color:red; font-weight:bold;'>Erreur de sécurité : Vous ne pouvez pas supprimer votre propre compte professionnel depuis l'interface web. Contactez l'administrateur système de la base de données.</div><br><a href='view.php?id=$id_pers'>Retour</a>");
}

$query = "SELECT nom_personnel, prenom_personnel, type_personnel FROM Personnel WHERE id_personnel = :id";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':id', $id_pers);
oci_execute($stmt, OCI_DEFAULT);
$employe = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$employe) {
    die("Employé introuvable dans le système.");
}

$message = '';
$error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete'])) {
    
    // Anonymisation (Soft-Delete) pour préserver l'historique (Animaux, Soins, Alimentation)
    // au lieu de réassigner au suppresseur ou de provoquer une erreur de contrainte.
    $sql_updates = [
        "UPDATE Personnel SET id_personnel_chef = NULL WHERE id_personnel_chef = :id",
        "UPDATE Personnel SET id_personnel_remplacant = NULL WHERE id_personnel_remplacant = :id",
        "DELETE FROM Etre_specialiste_de WHERE id_personnel = :id",
        "DELETE FROM Personnel_Boutique WHERE id_personnel = :id",
        // Écraser les données personnelles (nom, prénom) et bloquer le mot de passe
        // On n'efface pas l'historique_emploi ni les animaux à charge, pour garder la trace
        "UPDATE Personnel SET nom_personnel = 'SUPPRIME', prenom_personnel = 'PROFIL', pwd_personnel = 'DISABLED_ACCOUNT' WHERE id_personnel = :id"
    ];
    
    $error_occurred = false;
    foreach ($sql_updates as $req) {
        $st = oci_parse($conn, $req);
        oci_bind_by_name($st, ':id', $id_pers);
        $r = @oci_execute($st, OCI_COMMIT_ON_SUCCESS);
        
        if (!$r) {
            $e = oci_error($st);
            $message = "Erreur d'anonymisation : " . htmlentities($e['message']);
            $error_occurred = true;
            break;
        }
        oci_free_statement($st);
    }

    if (!$error_occurred) {
        // Redirection vers le listing après anonymisation
        header("Location: index.php?msg=deleted");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Licenciement / Suppression de Profil</title>
</head>
<body>
    <a href="view.php?id=<?php echo urlencode($id_pers); ?>">← Annuler et revenir à la vue détaillée</a>
    <h1>Résilier / Supprimer un profil Employé</h1>

    <?php if ($message): ?>
        <div style="background: #ffebee; border: 2px solid darkred; padding: 15px; color: darkred; font-size: 16px; margin-bottom: 20px;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <fieldset style="border-color:red; background: #fff0f0; padding:20px; width: 60%;">
        <legend style="color:red; font-weight:bold; font-size:1.2em;">⚠️ Zone de Danger - Action irréversible</legend>
        <p>Êtes-vous absolument sûr de vouloir supprimer définitivement ce compte du système :</p>
        <h2 style="margin:5px 0;">[<?php echo htmlspecialchars($employe['TYPE_PERSONNEL']); ?>] <?php echo htmlspecialchars($employe['PRENOM_PERSONNEL'] . " " . strtoupper($employe['NOM_PERSONNEL'])); ?></h2>
        
        <p><em>Rappel de procédure : Les mots de passe hashés et les clés associées liées à ce membre du personnel seront totalement détruits de la Base de Données Oracle. En vertu des règles applicables, cela provoquera une erreur stricte d'Oracle si l'employé reste l'unique référent d'un ou plusieurs animaux.</em></p>

        <form method="POST">
            <button type="submit" name="confirm_delete" style="background:#cc0000; color:white; font-weight:bold; padding:12px 20px; cursor:pointer; font-size:16px;">
                Oui, certifier la suppression de cet employé 
            </button>
        </form>
    </fieldset>
</body>
</html>