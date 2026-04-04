<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;
$id_pers = $_GET['id'] ?? null;

if (!$id_pers) {
    header("Location: index.php");
    exit;
}

// Récupérer l'employé
$query = "SELECT id_personnel, nom_personnel, prenom_personnel, type_personnel, salaire_personnel, 
                 TO_CHAR(date_entree_personnel, 'YYYY-MM-DD') as date_entree 
          FROM Personnel 
          WHERE id_personnel = :id";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':id', $id_pers);
oci_execute($stmt, OCI_DEFAULT);
$employe = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$employe) {
    die("Employé introuvable.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom_personnel']);
    $prenom = trim($_POST['prenom_personnel']);
    $salaire = $_POST['salaire_personnel'];
    $type = $_POST['type_personnel'];
    $mot_de_passe = $_POST['pwd_personnel'] ?? '';

    if (empty($nom) || empty($prenom) || empty($type)) {
        $error = true;
        $message = "Veuillez renseigner tous les champs obligatoires.";
    } else {
        $pwdSql = "";
        
        if (!empty($mot_de_passe)) {
            $hashee = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $pwdSql = ", pwd_personnel = :pwd";
        }

        $sql = "UPDATE Personnel 
                SET nom_personnel = :nom, 
                    prenom_personnel = :prenom, 
                    salaire_personnel = :salaire, 
                    type_personnel = :type 
                    $pwdSql 
                WHERE id_personnel = :id";
        
        $st = oci_parse($conn, $sql);
        oci_bind_by_name($st, ':nom', $nom);
        oci_bind_by_name($st, ':prenom', $prenom);
        oci_bind_by_name($st, ':salaire', $salaire);
        oci_bind_by_name($st, ':type', $type);
        oci_bind_by_name($st, ':id', $id_pers);
        
        if (!empty($mot_de_passe)) {
            oci_bind_by_name($st, ':pwd', $hashee);
        }

        $r = @oci_execute($st, OCI_COMMIT_ON_SUCCESS);
        if ($r) {
            $message = "Modifications enregistrées sur la fiche de {$prenom} {$nom}.";
            
            // Mise à jour de l'affichage local si on modifie pas de suite la DB
            $employe['NOM_PERSONNEL'] = $nom;
            $employe['PRENOM_PERSONNEL'] = $prenom;
            $employe['SALAIRE_PERSONNEL'] = $salaire;
            $employe['TYPE_PERSONNEL'] = $type;
        } else {
            $e = oci_error($st);
            $error = true;
            $message = "Erreur de mise à jour : " . htmlentities($e['message']);
        }
        oci_free_statement($st);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier employeur</title>
</head>
<body>
    <a href="view.php?id=<?php echo urlencode($id_pers); ?>">← Retour au profil employé</a>
    <h1>Modification Fiche Employeur : ID <?php echo htmlspecialchars($id_pers); ?></h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <p>
            <label>Nom de famille *:<br>
            <input type="text" name="nom_personnel" value="<?php echo htmlspecialchars($employe['NOM_PERSONNEL'] ?? ''); ?>" required size="30">
            </label>
        </p>

        <p>
            <label>Prénom(s) *:<br>
            <input type="text" name="prenom_personnel" value="<?php echo htmlspecialchars($employe['PRENOM_PERSONNEL'] ?? ''); ?>" required size="30">
            </label>
        </p>

        <p>
            <label>Changer le mot de passe (Laisser vide si inchangé) :<br>
            <input type="password" name="pwd_personnel" minlength="4">
            </label>
        </p>

        <p>
            <label>Corps de métier (Rôle et accès système) *:<br>
            <select name="type_personnel" required>
                <?php $t = strval($employe['TYPE_PERSONNEL'] ?? ''); ?>
                <option value="gérant" <?php if($t=='gérant') echo 'selected'; ?>>Gérant(e)</option>
                <option value="veterinaire" <?php if($t=='veterinaire') echo 'selected'; ?>>Vétérinaire</option>
                <option value="soignant" <?php if($t=='soignant') echo 'selected'; ?>>Soignant</option>
                <option value="technique" <?php if($t=='technique') echo 'selected'; ?>>Technique</option>
                <option value="boutique" <?php if($t=='boutique') echo 'selected'; ?>>Boutique</option>
            </select>
            </label>
        </p>

        <p>
            <label>Nouveau montant d'indemnité / Salaire * (€ net) :<br>
            <input type="number" step="0.01" min="10" name="salaire_personnel" value="<?php echo htmlspecialchars($employe['SALAIRE_PERSONNEL'] ?? ''); ?>" required>
            </label>
        </p>

        <p><button type="submit" style="background:orange; color:black; padding:10px 15px; cursor:pointer;">Valider Modifs de fiche</button></p>
    </form>
</body>
</html>