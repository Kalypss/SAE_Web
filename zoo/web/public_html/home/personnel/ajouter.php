<?php
require_once __DIR__ . '/../../../backend/Auth.php';
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$message = '';
$error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom_personnel']);
    $prenom = trim($_POST['prenom_personnel']);
    $mot_de_passe = $_POST['pwd_personnel'];
    $salaire = $_POST['salaire_personnel'];
    $type = $_POST['type_personnel'];
    $date_entree = $_POST['date_entree'];

    if (empty($nom) || empty($prenom) || empty($mot_de_passe) || empty($type) || empty($date_entree)) {
        $error = true;
        $message = "Veuillez renseigner tous les champs obligatoires.";
    } else {
        $hashee = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        // ID Management
        $sql_id = "SELECT NVL(MAX(id_personnel), 0) + 1 AS next_id FROM Personnel";
        $stmt_id = oci_parse($conn, $sql_id);
        oci_execute($stmt_id, OCI_DEFAULT);
        $row_id = oci_fetch_assoc($stmt_id);
        $next_id = $row_id['NEXT_ID'];
        oci_free_statement($stmt_id);

        $sql = "INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, pwd_personnel, salaire_personnel, type_personnel, date_entree_personnel) 
                VALUES (:id_personnel, :nom, :prenom, :pwd, :salaire, :type, TO_DATE(:date_entree, 'YYYY-MM-DD'))";
        $st = oci_parse($conn, $sql);
        oci_bind_by_name($st, ':id_personnel', $next_id);
        oci_bind_by_name($st, ':nom', $nom);
        oci_bind_by_name($st, ':prenom', $prenom);
        oci_bind_by_name($st, ':pwd', $hashee);
        oci_bind_by_name($st, ':salaire', $salaire);
        oci_bind_by_name($st, ':type', $type);
        oci_bind_by_name($st, ':date_entree', $date_entree);

        $r = @oci_execute($st, OCI_COMMIT_ON_SUCCESS);
        if ($r) {
            $message = "L'employé(e) {$prenom} {$nom} a bien été créé(e) dans l'annuaire.";
            header("refresh:2;url=index.php");
        } else {
            $e = oci_error($st);
            $error = true;
            $message = "Erreur lors de la création : " . htmlentities($e['message']);
        }
        oci_free_statement($st);

        // On peut éventuellement forcer une ligne dans Historique_emploi ici
        // Mais nous laisserons l'admin le faire dans la gestion de l'historique
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../styleajout.css">
    <title>Ajouter membre du personnel</title>
</head>
<body>
    <a href="index.php">← Retour à la liste du personnel</a>
    <h1>Création du profil d'embauche</h1>

    <?php if ($message): ?>
        <p style="color: <?php echo $error ? 'red' : 'green'; ?>; font-weight:bold;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <div class="formul">
        <p>
            <label>Nom de famille *:<br>
            <input type="text" name="nom_personnel" required size="30">
            </label>
        </p>

        <p>
            <label>Prénom(s) *:<br>
            <input type="text" name="prenom_personnel" required size="30">
            </label>
        </p>

        <p>
            <label>Mot de passe provisoire *:<br>
            <input type="password" name="pwd_personnel" required minlength="4">
            </label>
        </p>

        <p>
            <label>Corps de métier (Rôle et accès système) *:<br>
            <select name="type_personnel" required>
                <option value="">Sélectionner une affectation</option>
                <option value="gérant">Gérant(e) (Direction / Directeur Général)</option>
                <option value="veterinaire">Médecin Vétérinaire</option>
                <option value="soignant">Personnel Soignant / Soigneur Animalier</option>
                <option value="technique">Service Technique / Maintenance</option>
                <option value="boutique">Commercial Service Visiteurs / Boutique</option>
            </select>
            </label>
        </p>

        <p>
            <label>Salaire d'entrée / Fixe * (€ net/mois) :<br>
            <input type="number" step="0.01" min="10" name="salaire_personnel" required placeholder="Ex: 1900.50">
            </label>
        </p>

        <p>
            <label>Date de validation d'embauche (Début de contrat) * :<br>
            <input type="date" name="date_entree" required value="<?php echo date('Y-m-d'); ?>">
            </label>
        </p>
        </div>
        <p class="bouton"><button type="submit">Créer la Fiche Employé</button></p>
    </form>
</body>
</html>