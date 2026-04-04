<?php
require_once __DIR__ . '/../backend/Auth.php';
// Auth contrôle dynamiquement si on est connecté (et gère la redirection / suppression de cookies)
Auth::checkAccess(); // Exemple : "tous" permet à tout profil connecté d'accéder au dashboard

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
    <a href="home/profil/view.php">Profil</a>
    <a href="home/recherche.php">Recherche Transversale</a>

    <h2>Gestion Administrative et Métier</h2>
    <nav>
        <ul>
            <li><h3>Animaux et Espèces</h3>
                <ul>
                    <li><a href="home/animaux/index.php">Liste des Animaux</a></li>
                    <li><a href="home/especes/index.php">Liste des Espèces</a></li>
                    <li><a href="home/soins/index.php">Dossiers de Soins</a></li>
                    <li><a href="home/alimentation/index.php">Alimentation et Rations</a></li>
                </ul>
            </li>
            <li><h3>Infrastructures</h3>
                <ul>
                    <li><a href="home/zones/index.php">Zones du Zoo</a></li>
                    <li><a href="home/enclos/index.php">Liste des Enclos</a></li>
                    <li><a href="home/reparations/index.php">Registre des Réparations</a></li>
                </ul>
            </li>
            <li><h3>Personnel et Intervenants</h3>
                <ul>
                    <li><a href="home/personnel/index.php">Membres du Personnel</a></li>
                    <li><a href="home/prestataires/index.php">Liste des Prestataires</a></li>
                </ul>
            </li>
            <li><h3>Boutique et Visiteurs</h3>
                <ul>
                    <li><a href="home/visiteurs/index.php">Registre des Visiteurs</a></li>
                    <li><a href="home/boutiques/index.php">Liste des Boutiques</a></li>
                    <li><a href="home/prestations/index.php">Prestations (Billet, Pass)</a></li>
                    <li><a href="home/parrainages/index.php">Parrainages d'animaux</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</body>
</html>
