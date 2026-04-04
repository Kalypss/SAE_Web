<?php
require_once 'connex.inc.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $id = $_POST['id_personnel'] ?? '';
    $nom = $_POST['nom_personnel'] ?? '';
    $prenom = $_POST['prenom_personnel'] ?? '';
    $mdp = $_POST['pwd_personnel'] ?? '';
    $type = $_POST['type_personnel'] ?? 'soignant';
    
    if (!empty($id) && !empty($mdp) && !empty($nom) && !empty($prenom)) {
        // Hachage du mot de passe en sha256 comme dans le fichier home.php
        $hash = hash('sha256', $mdp);
        
        // Connexion à Oracle
        $idcom = connex("zoo", "myparam");
        if ($idcom) {
            // Insertion dans la table Personnel (ajout de salaire_personnel pour respecter le CHECK)
            $query = "INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, pwd_personnel, type_personnel, date_entree_personnel, salaire_personnel) 
                      VALUES (:id, :nom, :prenom, :pwd, :type, SYSDATE, 2000)";
            
            $stmt = oci_parse($idcom, $query);
            
            oci_bind_by_name($stmt, ':id', $id);
            oci_bind_by_name($stmt, ':nom', $nom);
            oci_bind_by_name($stmt, ':prenom', $prenom);
            oci_bind_by_name($stmt, ':pwd', $hash);
            oci_bind_by_name($stmt, ':type', $type);
            
            if (oci_execute($stmt)) {
                $message = "Succès ! L'employé '$prenom $nom' (ID: $id) a été ajouté. Vous pouvez maitenant vous connecter !";
            } else {
                $e = oci_error($stmt);
                $message = "Erreur lors de l'ajout : " . htmlentities($e['message']);
            }
            oci_free_statement($stmt);
            oci_close($idcom);
        } else {
            $message = "Erreur de connexion à la base de données.";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Debug - Créer un employé</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background-color: white; padding: 20px 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #333; }
        label { display: block; margin-bottom: 5px; color: #666; font-weight: bold; }
        input[type="text"], input[type="number"], input[type="password"], select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        input[type="submit"] { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        input[type="submit"]:hover { background-color: #218838; }
        .msg { padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        .msg.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .msg.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Rajouter un employé</h2>
        
        <?php if ($message): ?>
            <div class="msg <?php echo strpos($message, 'Succès') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>ID de connexion (ex: 1) :</label>
            <input type="number" name="id_personnel" required>
            
            <label>Nom :</label>
            <input type="text" name="nom_personnel" required>
            
            <label>Prénom :</label>
            <input type="text" name="prenom_personnel" required>
            
            <label>Mot de passe (sera hashé) :</label>
            <input type="password" name="pwd_personnel" required>
            
            <label>Rôle / Type de poste :</label>
            <select name="type_personnel">
                <option value="soignant">Soignant</option>
                <option value="veterinaire">Vétérinaire</option>
                <option value="boutique">Boutique</option>
                <option value="technique">Technique</option>
                <option value="gérant">Gérant</option>
            </select>
            
            <input type="submit" name="ajouter" value="Créer l'employé">
        </form>
        
        <a class="back-link" href="authentif.php">Retour à la page de connexion</a>
    </div>
</body>
</html>