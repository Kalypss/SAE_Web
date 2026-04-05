<?php
ini_set('display_errors', 0); // Ne pas afficher d'erreurs en prod
error_reporting(E_ALL);

require_once __DIR__ . '/../backend/Security.php';

// Si l'utilisateur a déjà une session active, on le redirige directement vers le dashboard
if (isset($_SESSION['user_id']) && !empty($_SESSION['role'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';  // Connexion activée
$security = new Security();

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ip = $_SERVER['REMOTE_ADDR'];

    // Vérifie le rate limiting
    if (!$security->checkRateLimit($ip)) {
        $message = "Trop de tentatives de connexion échouées. Veuillez réessayer plus tard.";
    } else {
        // Validation et assainissement des entrées avec des expressions régulières (Regex)
        $raw_username = $_POST['username'] ?? '';
        $raw_password = $_POST['password'] ?? '';

        $username = $security->sanitizeInput($raw_username);
        $password = $raw_password; // ATTENTION: On n'assainit JAMAIS un mot de passe (les caractères spéciaux sont autorisés et le hachage sécurise l'entrée)

        // Validation Regex stricte : s'assure que l'identifiant est purement numérique
        if (!preg_match('/^[0-9]+$/', $username)) {
            $message = "Format d'identifiant invalide (Doit être un numéro).";
            $security->logAction($ip, $raw_username, 'INVALID FORMAT (Regex match fails)');
            $security->incrementFailedAttempt();
        } else {
            // Requête préparée pour éviter l'injection SQL (recherche par ID_PERSONNEL)
            $sql = "SELECT id_personnel, nom_personnel, prenom_personnel, pwd_personnel, type_personnel FROM Personnel WHERE id_personnel = :userid";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ':userid', $username);
            
            oci_execute($stmt, OCI_DEFAULT); // On utilise OCI_DEFAULT pour ne pas auto-commit sans raison
            
            $user = oci_fetch_assoc($stmt);
            
            // Hachage du mot de passe en sha256 (comme lors de la création d'employé initial)
            $hashed_password_sha256 = hash('sha256', $password);
            
            // On vérifie le format bcrypt récent (password_verify) OU l'ancien format sha256
            if ($user && (password_verify($password, trim($user['PWD_PERSONNEL'])) || $hashed_password_sha256 === trim($user['PWD_PERSONNEL']))) {
                // Succès
                $security->resetAttempts();
                $security->logAction($ip, $username, 'SUCCESS');
                
                $_SESSION['user_id'] = $user['ID_PERSONNEL'];
                $_SESSION['prenom'] = $user['PRENOM_PERSONNEL'];
                $_SESSION['nom'] = $user['NOM_PERSONNEL'];
                $_SESSION['role'] = strtolower($user['TYPE_PERSONNEL']); // veterinaire, gérant, etc.
                
                // Redirection vers home.php (on s'assure qu'aucun output n'a eu lieu avant)
                header('Location: dashboard.php');
                exit;
            } else {
                // Échec
                $security->incrementFailedAttempt();
                $security->logAction($ip, $username, 'FAILED (Wrong credentials)');
                $message = "Identifiants invalides.";
                }
            oci_free_statement($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Connexion</title>
</head>
<body>
    <div class="gauche">

    </div>
    <div class="droite">
    <h1>Connexion</h1>
    
    <?php if (!empty($message)): ?>
        <p style="color: red;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="username">Identifiant</label><br>
        <input type="text" id="username" name="username" required><br><br>
        
        <label for="password">Mot de passe</label><br>
        <input type="password" id="password" name="password" required><br><br>
        
        <input type="submit" value="Se connecter" id="connex">

    </form>
    <a href="mdp_oublie">mot de passe oublié ?</a>
    <p class="condition"><br>En vous connectant vous accepter bien évidemment <a href="trup.php">les conditions générales de la vente de vôtre âme au diable</a></p>
</div>
</body>
</html>
