<?php
session_start();
// Vérifier si utilisateur non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /public_html/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accès Refusé</title>
    <style>
        body { font-family: 'Poppins', sans-serif; text-align: center; margin-top: 50px; background-color: #000; color: #fff; }
        h1 { color: #ff6b6b; }
        a { color: #7C6BEE; }
    </style>
    <script>
        // Redirection vers le dashboard après 5 secondes
        setTimeout(function() {
            window.location.href = "/public_html/dashboard.php";
        }, 5000);
    </script>
</head>
<body>
    <h1>⊘ Accès refusé</h1>
    <p>Vous n'avez pas les droits nécessaires pour effectuer cette action.</p>
    <p><em>Vous allez être redirigé vers la page principale dans 5 secondes...</em></p>
    <a href="/public_html/dashboard.php">Ou cliquez ici pour revenir tout de suite</a>
</body>
</html>