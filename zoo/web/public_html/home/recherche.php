<?php
require_once __DIR__ . '/../../backend/Auth.php';
// Accessible à tout le monde, la page filtrera les résultats selon le rôle.
Auth::checkAccess();

require_once __DIR__ . '/../../config/database.php';

$role = $_SESSION['role'] ?? '';
$searchQuery = $_GET['q'] ?? '';

$results = [
    'Animaux' => [],
    'Espèces' => [],
    'Personnel' => [],
    'Visiteurs' => []
];

// --- ALGORITHME DE RECHERCHE FLOUE (Fuzzy Search) ---
// Utilisation de la distance de Levenshtein 
// (plus adapté que la distance de Hamming car elle gère l'ajout ou l'oubli de lettres modifiant la taille du mot)
function isFuzzyMatch($search, $subject) {
    if (empty($search) || empty($subject)) return false;
    $search = strtolower(trim($search));
    $subject = strtolower(trim($subject));
    
    // 1. Correspondance exacte ou partielle
    if (strpos($subject, $search) !== false) return true;
    
    // 2. Vérification par distance de Levenshtein mot par mot
    // Tolérance : 1 faute si le mot est court, 2 fautes si le mot est long
    $tolerance = (strlen($search) <= 4) ? 1 : 2;
    $words = explode(' ', $subject);
    
    foreach ($words as $word) {
        // On vérifie la similarité uniquement si les mots ont une taille proche
        if (abs(strlen($search) - strlen($word)) <= $tolerance) {
            if (levenshtein($search, $word) <= $tolerance) {
                return true;
            }
        }
    }
    return false;
}

if (!empty(trim($searchQuery))) {
    
    // 1. Animaux : Accessible pour [gérant, veterinaire, soignant, technique]
    if (in_array($role, ['gérant', 'veterinaire', 'soignant', 'technique'])) {
        $sql = "SELECT id_animaux, nom_animal, rfid_animal FROM Animaux";
        $stmt = oci_parse($conn, $sql);
        oci_execute($stmt, OCI_DEFAULT);
        while ($row = oci_fetch_assoc($stmt)) {
            if (isFuzzyMatch($searchQuery, $row['NOM_ANIMAL'] ?? '') || isFuzzyMatch($searchQuery, $row['RFID_ANIMAL'] ?? '')) {
                $results['Animaux'][] = $row;
            }
        }
        oci_free_statement($stmt);
    }
    
    // 2. Espèces : Accessible pour [gérant, veterinaire, technique]
    if (in_array($role, ['gérant', 'veterinaire', 'technique'])) {
        $sql = "SELECT id_espece, nomu_espece, noml_espece FROM Especes";
        $stmt = oci_parse($conn, $sql);
        oci_execute($stmt, OCI_DEFAULT);
        while ($row = oci_fetch_assoc($stmt)) {
            if (isFuzzyMatch($searchQuery, $row['NOMU_ESPECE'] ?? '') || isFuzzyMatch($searchQuery, $row['NOML_ESPECE'] ?? '')) {
                $results['Espèces'][] = $row;
            }
        }
        oci_free_statement($stmt);
    }

    // 3. Personnel : Accessible pour [gérant, technique]
    if (in_array($role, ['gérant', 'technique'])) {
        $sql = "SELECT id_personnel, nom_personnel, prenom_personnel, type_personnel FROM Personnel";
        $stmt = oci_parse($conn, $sql);
        oci_execute($stmt, OCI_DEFAULT);
        while ($row = oci_fetch_assoc($stmt)) {
            $fullName = ($row['PRENOM_PERSONNEL'] ?? '') . ' ' . ($row['NOM_PERSONNEL'] ?? '');
            if (isFuzzyMatch($searchQuery, $fullName) || isFuzzyMatch($searchQuery, $row['TYPE_PERSONNEL'] ?? '')) {
                $results['Personnel'][] = $row;
            }
        }
        oci_free_statement($stmt);
    }

    // 4. Visiteurs : Accessible pour [gérant, boutique, technique]
    if (in_array($role, ['gérant', 'boutique', 'technique'])) {
        $sql = "SELECT id_visiteur, nom_visiteur, prenom_visiteur, email_visiteur FROM Visiteur";
        $stmt = oci_parse($conn, $sql);
        oci_execute($stmt, OCI_DEFAULT);
        while ($row = oci_fetch_assoc($stmt)) {
            $fullName = ($row['PRENOM_VISITEUR'] ?? '') . ' ' . ($row['NOM_VISITEUR'] ?? '');
            if (isFuzzyMatch($searchQuery, $fullName) || isFuzzyMatch($searchQuery, $row['EMAIL_VISITEUR'] ?? '')) {
                $results['Visiteurs'][] = $row;
            }
        }
        oci_free_statement($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche Globale - Zoo'land</title>
</head>
<body>

    <a href="dashboard.php" class="back-link">← Retour au dashboard</a>

    <div class="search-container">
        <h1>Recherche Globale</h1>
        <form action="recherche.php" method="GET">
            <input type="text" name="q" placeholder="Rechercher un employé, un animal..." value="<?php echo htmlspecialchars($searchQuery); ?>" required>
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <?php if (!empty($searchQuery)): ?>
        <p>Résultats pour : <strong>"<?php echo htmlspecialchars($searchQuery); ?>"</strong></p>

        <!-- Section Animaux -->
        <?php if (in_array($role, ['gérant', 'veterinaire', 'soignant', 'technique'])): ?>
            <div class="category-section">
                <h2>Animaux (<?php echo count($results['Animaux']); ?>)</h2>
                <ul class="result-list">
                    <?php if (count($results['Animaux']) > 0): ?>
                        <?php foreach ($results['Animaux'] as $animal): ?>
                            <li class="result-item">
                                <div class="result-item-main">
                                    <strong><?php echo htmlspecialchars($animal['NOM_ANIMAL'] ?? '—'); ?></strong>
                                    <div class="result-item-sub">RFID : <?php echo htmlspecialchars($animal['RFID_ANIMAL'] ?? 'N/A'); ?></div>
                                </div>
                                <a href="/public_html/animaux/view.php?id=<?php echo urlencode($animal['ID_ANIMAUX']); ?>" class="btn-view">Consulter</a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-result">Aucun animal trouvé.</div>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Section Espèces -->
        <?php if (in_array($role, ['gérant', 'veterinaire', 'technique'])): ?>
            <div class="category-section">
                <h2>Espèces (<?php echo count($results['Espèces']); ?>)</h2>
                <ul class="result-list">
                    <?php if (count($results['Espèces']) > 0): ?>
                        <?php foreach ($results['Espèces'] as $espece): ?>
                            <li class="result-item">
                                <div class="result-item-main">
                                    <strong><?php echo htmlspecialchars($espece['NOMU_ESPECE'] ?? '—'); ?></strong>
                                    <div class="result-item-sub">Nom latin : <em><?php echo htmlspecialchars($espece['NOML_ESPECE'] ?? '—'); ?></em></div>
                                </div>
                                <a href="/public_html/especes/view.php?id=<?php echo urlencode($espece['ID_ESPECE']); ?>" class="btn-view">Consulter</a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-result">Aucune espèce trouvée.</div>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Section Personnel -->
        <?php if (in_array($role, ['gérant', 'technique'])): ?>
            <div class="category-section">
                <h2>Personnel (<?php echo count($results['Personnel']); ?>)</h2>
                <ul class="result-list">
                    <?php if (count($results['Personnel']) > 0): ?>
                        <?php foreach ($results['Personnel'] as $pers): ?>
                            <li class="result-item">
                                <div class="result-item-main">
                                    <strong><?php echo htmlspecialchars($pers['PRENOM_PERSONNEL'] . ' ' . $pers['NOM_PERSONNEL']); ?></strong>
                                    <div class="result-item-sub">Rôle : <?php echo htmlspecialchars(ucfirst($pers['TYPE_PERSONNEL'])); ?></div>
                                </div>
                                <a href="/public_html/personnel/view.php?id=<?php echo urlencode($pers['ID_PERSONNEL']); ?>" class="btn-view">Consulter</a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-result">Aucun membre du personnel trouvé.</div>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Section Visiteurs -->
        <?php if (in_array($role, ['gérant', 'boutique', 'technique'])): ?>
            <div class="category-section">
                <h2>Visiteurs / Clients (<?php echo count($results['Visiteurs']); ?>)</h2>
                <ul class="result-list">
                    <?php if (count($results['Visiteurs']) > 0): ?>
                        <?php foreach ($results['Visiteurs'] as $visiteur): ?>
                            <li class="result-item">
                                <div class="result-item-main">
                                    <strong><?php echo htmlspecialchars($visiteur['PRENOM_VISITEUR'] . ' ' . $visiteur['NOM_VISITEUR']); ?></strong>
                                    <div class="result-item-sub">Email : <?php echo htmlspecialchars($visiteur['EMAIL_VISITEUR'] ?? 'N/A'); ?></div>
                                </div>
                                <a href="/public_html/visiteurs/view.php?id=<?php echo urlencode($visiteur['ID_VISITEUR']); ?>" class="btn-view">Consulter</a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-result">Aucun visiteur trouvé.</div>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</body>
</html>