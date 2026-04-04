<?php
require_once __DIR__ . '/../backend/Auth.php';
// Auth contrôle dynamiquement si on est connecté (et gère la redirection / suppression de cookies)
Auth::checkAccess(['tous']); // Exemple : "tous" permet à tout profil connecté d'accéder au dashboard

require_once __DIR__ . '/../config/database.php';

// Récupérer formellement l'utilisateur depuis la DB pour afficher "toutes les infos de l'employé"
$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM Personnel WHERE id_personnel = :id_personnel";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id_personnel', $userId);
oci_execute($stmt, OCI_DEFAULT);
$employee = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

// Si par hasard l'employé n'existe plus en Base
if (!$employee) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard <?php echo htmlspecialchars($employee['TYPE_PERSONNEL']); ?></title>
</head>
<body>
    <h1>Bonjour, <?php echo htmlspecialchars($employee['PRENOM_PERSONNEL']); ?> !</h1>
    
    <h2>Vos informations</h2>
    <ul>
        <li><strong>Identifiant (Nom) :</strong> <?php echo htmlspecialchars($employee['NOM_PERSONNEL']); ?></li>
        <li><strong>Prénom :</strong> <?php echo htmlspecialchars($employee['PRENOM_PERSONNEL']); ?></li>
        <li><strong>ID Personnel :</strong> <?php echo htmlspecialchars($employee['ID_PERSONNEL']); ?></li>
        <li><strong>Salaire :</strong> <?php echo htmlspecialchars($employee['SALAIRE_PERSONNEL']); ?> €</li>
        <!-- Ajoutez ici les autres colonnes de la base de données selon votre schéma -->
    </ul>
    
    <a href="logout.php">Se déconnecter</a>
    <a href="/profil/view.php">Profil</a>
    <a href="recherche.php">recherche</a>
</body>
</html>
