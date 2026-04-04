<?php
require_once __DIR__ . '/../../backend/Auth.php';
// Accessible à tout le monde
Auth::checkAccess(['tous']);

require_once __DIR__ . '/../../config/database.php';

$userId = $_SESSION['user_id'];
$message = '';
$isError = false;

// Traitement POST pour la mise à jour (Nom, Prénom uniquement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    
    if (!empty($nom) && !empty($prenom)) {
        $sql = "UPDATE Personnel SET nom_personnel = :nom, prenom_personnel = :prenom WHERE id_personnel = :id";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':nom', $nom);
        oci_bind_by_name($stmt, ':prenom', $prenom);
        oci_bind_by_name($stmt, ':id', $userId);
        
        if (oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) { // Commit direct car mise à jour simple
            // Update session values
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;
            
            $message = "Les informations ont bien été mises à jour.";
            $isError = false;
        } else {
            $message = "Erreur lors de la mise à jour dans la base de données. Réessayez.";
            $isError = true;
        }
        oci_free_statement($stmt);
    } else {
        $message = "Les champs nom et prénom ne peuvent pas être vides.";
        $isError = true;
    }
}

// Récupérer les infos actuelles pour pré-remplir la page
$sqlInfo = "SELECT id_personnel, nom_personnel, prenom_personnel, type_personnel, date_entree_personnel, salaire_personnel FROM Personnel WHERE id_personnel = :id_personnel";
$stmtInfo = oci_parse($conn, $sqlInfo);
oci_bind_by_name($stmtInfo, ':id_personnel', $userId);
oci_execute($stmtInfo, OCI_DEFAULT);
$employee = oci_fetch_assoc($stmtInfo);
oci_free_statement($stmtInfo);

if (!$employee) {
    header("Location: ../index.php"); // Si par miracle l'employé est supprimé de la BDD pendant qu'il était en ligne
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil</title>

</head>
<body>
    <div class="container">
        <h1>Mon Profil</h1>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $isError ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>ID Employé :</label>
            <input type="number" value="<?php echo htmlspecialchars($employee['ID_PERSONNEL']); ?>" disabled>
            
            <label>Rôle actuel :</label>
            <input type="text" value="<?php echo htmlspecialchars(ucfirst($employee['TYPE_PERSONNEL'])); ?>" disabled>

            <label>Date de recrutement :</label>
            <input type="text" value="<?php echo htmlspecialchars($employee['DATE_ENTREE_PERSONNEL']); ?>" disabled>

            <!-- Seulement le nom et le prénom sont modifiables ! -->
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($employee['NOM_PERSONNEL']); ?>" required>

            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($employee['PRENOM_PERSONNEL']); ?>" required>

            <button type="submit" name="update_profile">Enregistrer les modifications</button>
        </form>

        <a href="../dashboard.php" class="back-link">← Retour au dashboard</a>
    </div>
</body>
</html>