<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$userId = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $error = "Les nouveaux mots de passe ne correspondent pas.";
    } elseif (strlen($new_password) < 8) {
        $error = "Le nouveau mot de passe doit faire au moins 8 caractères.";
    } else {
        $sql = "SELECT mot_de_passe_personnel FROM Personnel WHERE id_personnel = :id";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':id', $userId);
        oci_execute($stmt, OCI_DEFAULT);
        $user = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);

        if (!password_verify($current_password, $user['MOT_DE_PASSE_PERSONNEL'])) {
            $error = "Le mot de passe actuel est incorrect.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $update_sql = "UPDATE Personnel SET mot_de_passe_personnel = :hash WHERE id_personnel = :id";
            $upd_stmt = oci_parse($conn, $update_sql);
            oci_bind_by_name($upd_stmt, ':hash', $hashed);
            oci_bind_by_name($upd_stmt, ':id', $userId);
            
            if (oci_execute($upd_stmt, OCI_COMMIT_ON_SUCCESS)) {
                $message = "Mot de passe mis à jour avec succès.";
            } else {
                $error = "Erreur lors de la mise à jour.";
            }
            oci_free_statement($upd_stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mon mot de passe</title>
</head>
<body>
    <h1>Modifier mon mot de passe</h1>
    <a href="/public_html/dashboard.php">Retour au Dashboard</a>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if ($message): ?>
        <p style="color:green;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST">
        <div>
            <label>Mot de passe actuel :</label>
            <input type="password" name="current_password" required>
        </div>
        <div>
            <label>Nouveau mot de passe :</label>
            <input type="password" name="new_password" required minlength="8">
        </div>
        <div>
            <label>Confirmer nouveau mot de passe :</label>
            <input type="password" name="confirm_password" required minlength="8">
        </div>
        <button type="submit">Valider</button>
    </form>
</body>
</html>
