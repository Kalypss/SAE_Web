<?php
require_once __DIR__ . '/../../../backend/Auth.php';
// Vérification automatique des droits grâce au fichier permissions.php
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$role = $_SESSION['role'] ?? '';
$userId = $_SESSION['user_id'] ?? '';

// --- RECUPERATION DES FILTRES DE LA REQUETE GET ---
$search = $_GET['search'] ?? '';
$filter_espece = $_GET['espece'] ?? '';
$filter_enclos = $_GET['enclos'] ?? '';
$filter_regime = $_GET['regime'] ?? '';
$filter_menace = isset($_GET['menace']) ? 1 : 0;

// --- GESTION DU TRI ---
$sort_mapping = [
    'nom' => 'LOWER(a.nom_animal)',
    'poids' => 'a.poids_animal',
    'dob' => 'a.dob_animal'
];
$sort = (isset($_GET['sort']) && isset($sort_mapping[$_GET['sort']])) ? $_GET['sort'] : 'nom';
$order = (isset($_GET['order']) && $_GET['order'] === 'desc') ? 'DESC' : 'ASC';
$sort_sql = $sort_mapping[$sort] . ' ' . $order;

// Fonction pour générer les liens de tri (garde les filtres actifs)
function sortLink($col, $current_sort, $current_order) {
    $params = $_GET;
    $params['sort'] = $col;
    $params['order'] = ($current_sort === $col && $current_order === 'asc') ? 'desc' : 'asc';
    return '?' . http_build_query($params);
}

// Fonction utilitaire pour couleurs de régime (badges)
function getRegimeColor($regime) {
    $colors = ['carnivore' => 'red', 'vegetarien' => 'green', 'omnivore' => 'orange', 'insectivore' => 'saddlebrown', 'filtreur' => 'blue'];
    return $colors[strtolower($regime)] ?? 'black';
}

// --- CONSTRUCTION DE LA REQUETE DYNAMIQUE ---
$base_sql = "SELECT a.id_animaux, a.nom_animal, TO_CHAR(a.dob_animal, 'DD/MM/YYYY') as dob_format, a.poids_animal, 
                    a.regime_alimentaire_animal, a.rfid_animal,
                    e.nomu_espece, e.est_menace, 
                    enc.id_enclo, z.nom_zone, 
                    p.nom_personnel, p.prenom_personnel
             FROM Animaux a
             JOIN Especes e ON a.id_espece = e.id_espece
             JOIN Enclos enc ON a.id_enclo = enc.id_enclo
             JOIN Zone z ON enc.id_zone = z.id_zone
             JOIN Personnel p ON a.id_personnel = p.id_personnel
             WHERE 1=1 ";

$where_clauses = [];
$binds = [];

// Règle Soignant : Ne voit que SES animaux
if ($role === 'soignant') {
    $where_clauses[] = "a.id_personnel = :user_id";
    $binds[':user_id'] = $userId;
}

if (!empty(trim($search))) {
    $where_clauses[] = "(LOWER(a.nom_animal) LIKE LOWER(:search) OR LOWER(a.rfid_animal) LIKE LOWER(:search))";
    $binds[':search'] = '%' . trim($search) . '%';
}

if (!empty($filter_espece)) {
    $where_clauses[] = "a.id_espece = :espece";
    $binds[':espece'] = $filter_espece;
}

if (!empty($filter_enclos)) {
    $where_clauses[] = "a.id_enclo = :enclos";
    $binds[':enclos'] = $filter_enclos;
}

if (!empty($filter_regime)) {
    $where_clauses[] = "a.regime_alimentaire_animal = :regime";
    $binds[':regime'] = $filter_regime;
}

if ($filter_menace === 1) {
    $where_clauses[] = "e.est_menace = 1";
}

if (!empty($where_clauses)) {
    $base_sql .= " AND " . implode(" AND ", $where_clauses);
}

$base_sql .= " ORDER BY " . $sort_sql;

$stmt = oci_parse($conn, $base_sql);
foreach ($binds as $key => $val) {
    oci_bind_by_name($stmt, $key, $binds[$key]); 
}
oci_execute($stmt, OCI_DEFAULT);

$animaux = [];
while ($row = oci_fetch_assoc($stmt)) {
    $animaux[] = $row;
}
oci_free_statement($stmt);

// --- DONNÉES POUR LES LISTES DÉROULANTES DES FILTRES ---
$especes_list = [];
$st_esp = oci_parse($conn, "SELECT id_espece, nomu_espece FROM Especes ORDER BY nomu_espece");
oci_execute($st_esp, OCI_DEFAULT);
while ($r = oci_fetch_assoc($st_esp)) { $especes_list[] = $r; }
oci_free_statement($st_esp);

$enclos_list = [];
$st_enc = oci_parse($conn, "SELECT enc.id_enclo, z.nom_zone FROM Enclos enc JOIN Zone z ON enc.id_zone = z.id_zone ORDER BY enc.id_enclo");
oci_execute($st_enc, OCI_DEFAULT);
while ($r = oci_fetch_assoc($st_enc)) { $enclos_list[] = $r; }
oci_free_statement($st_enc);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Animaux</title>
</head>
<body>
    <a href="/public_html/dashboard.php">← Retour au dashboard</a>
    <h1>Liste des Animaux</h1>

    <?php if (in_array($role, ['gérant', 'veterinaire'])): ?>
        <a href="/public_html/home/animaux/ajouter.php">Ajouter un animal</a>
        <br><br>
    <?php endif; ?>

    <!-- FORMULAIRE DE FILTRAGE -->
    <fieldset>
        <legend>Recherche & Filtres</legend>
        <form method="GET" action="index.php">
            <!-- Conserver le tri actuel dans le formulaire -->
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
            <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">

            <label>Mot-clé (Nom, RFID):
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>">
            </label>

            <label>Espèce:
                <select name="espece">
                    <option value="">-- Toutes --</option>
                    <?php foreach($especes_list as $esp): ?>
                        <option value="<?php echo $esp['ID_ESPECE']; ?>" <?php if((string)$filter_espece === (string)$esp['ID_ESPECE']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($esp['NOMU_ESPECE']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Enclos:
                <select name="enclos">
                    <option value="">-- Tous --</option>
                    <?php foreach($enclos_list as $enc): ?>
                        <option value="<?php echo $enc['ID_ENCLO']; ?>" <?php if((string)$filter_enclos === (string)$enc['ID_ENCLO']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars("Enclos n°" . $enc['ID_ENCLO'] . " (" . $enc['NOM_ZONE'] . ")"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Régime:
                <select name="regime">
                    <option value="">-- Tous --</option>
                    <?php $regimes = ['vegetarien', 'carnivore', 'insectivore', 'filtreur', 'omnivore']; ?>
                    <?php foreach($regimes as $r): ?>
                        <option value="<?php echo $r; ?>" <?php if($filter_regime === $r) echo 'selected'; ?>><?php echo ucfirst($r); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                <input type="checkbox" name="menace" value="1" <?php if($filter_menace === 1) echo 'checked'; ?>> Espèces menacées seulement
            </label>

            <button type="submit">Filtrer</button>
            <a href="index.php">Réinitialiser</a>
        </form>
    </fieldset>
    <br>

    <!-- TABLEAU -->
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th><a href="<?php echo sortLink('nom', $sort, $order); ?>">Nom de l'animal</a></th>
                <th>Espèce</th>
                <th>Enclos</th>
                <th>RFID</th>
                <?php if ($role !== 'soignant'): ?>
                    <th>Soignant responsable</th>
                <?php endif; ?>
                <th><a href="<?php echo sortLink('dob', $sort, $order); ?>">Date de naissance</a></th>
                <th><a href="<?php echo sortLink('poids', $sort, $order); ?>">Poids (kg)</a></th>
                <th>Régime alimentaire</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($animaux) > 0): ?>
                <?php foreach ($animaux as $animal): ?>
                    <tr>
                        <!-- Nom (Fallback "—") -->
                        <td><?php echo (!empty(trim($animal['NOM_ANIMAL']))) ? htmlspecialchars($animal['NOM_ANIMAL']) : '—'; ?></td>
                        
                        <!-- Espèce avec Badge Rouge (gras basique sans css externe) si menacée -->
                        <td>
                            <?php echo htmlspecialchars($animal['NOMU_ESPECE']); ?>
                            <?php if ($animal['EST_MENACE'] == 1): ?>
                                &nbsp;<span style="color: white; background-color: red; padding: 2px 5px; border-radius: 3px; font-size: 0.8em; font-weight: bold;">MENACÉE</span>
                            <?php endif; ?>
                        </td>

                        <!-- Lien vers Enclos -->
                        <td>
                            <a href="/public_html/home/enclos/view.php?id=<?php echo urlencode($animal['ID_ENCLO']); ?>">
                                Enclos n°<?php echo htmlspecialchars($animal['ID_ENCLO']); ?> <br>
                                <small>(<?php echo htmlspecialchars($animal['NOM_ZONE'] ?? '?'); ?>)</small>
                            </a>
                        </td>

                        <!-- RFID Copiable Monospace -->
                        <td>
                            <span style="font-family: monospace; cursor: pointer; border-bottom: 1px dashed #ccc;" 
                                  onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($animal['RFID_ANIMAL']); ?>'); alert('RFID Copié!');" 
                                  title="Cliquer pour copier">
                                <?php echo htmlspecialchars($animal['RFID_ANIMAL']); ?>
                            </span>
                        </td>

                        <!-- Soignant (masqué pour le role soignant) -->
                        <?php if ($role !== 'soignant'): ?>
                            <td><?php echo htmlspecialchars($animal['PRENOM_PERSONNEL'] . ' ' . $animal['NOM_PERSONNEL']); ?></td>
                        <?php endif; ?>

                        <!-- Date de Naissance formatée -->
                        <td><?php echo htmlspecialchars($animal['DOB_FORMAT']); ?></td>

                        <!-- Poids -->
                        <td><?php echo htmlspecialchars($animal['POIDS_ANIMAL']); ?> kg</td>

                        <!-- Régime Alimentaire Badge -->
                        <td>
                            <span style="color: white; background-color: <?php echo getRegimeColor($animal['REGIME_ALIMENTAIRE_ANIMAL']); ?>; padding: 3px 6px; border-radius: 4px; font-size: 0.9em; font-weight: bold;">
                                <?php echo htmlspecialchars(ucfirst($animal['REGIME_ALIMENTAIRE_ANIMAL'])); ?>
                            </span>
                        </td>

                        <!-- Actions selon les droits -->
                        <td>
                            <a href="/public_html/home/animaux/view.php?id=<?php echo urlencode($animal['ID_ANIMAUX']); ?>">Voir</a>
                            
                            <?php if (in_array($role, ['gérant', 'veterinaire'])): ?>
                                | <a href="/public_html/home/animaux/modifier.php?id=<?php echo urlencode($animal['ID_ANIMAUX']); ?>">Modifier</a>
                            <?php endif; ?>
                            
                            <?php if ($role === 'gérant'): ?>
                                | <a href="/public_html/home/animaux/supprimer.php?id=<?php echo urlencode($animal['ID_ANIMAUX']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet animal ?');">Supprimer</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo ($role === 'soignant') ? '8' : '9'; ?>">Aucun animal trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>