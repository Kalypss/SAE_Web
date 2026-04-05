<?php
require_once __DIR__ . '/../backend/Auth.php';
Auth::checkAccess();

require_once __DIR__ . '/../config/database.php';

$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM Personnel WHERE id_personnel = :id_personnel";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id_personnel', $userId);
oci_execute($stmt, OCI_DEFAULT);
$employee = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$employee) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$role = strtolower($_SESSION['role']);
$permissions = require __DIR__ . '/../config/permissions.php';

function hasAccess($path) {
    global $role, $permissions;
    if ($role === 'gérant') return true;
    $rolesAllowed = $permissions[$path] ?? [];
    return in_array('tous', $rolesAllowed) || in_array($role, $rolesAllowed);
}


$kpis = [];

function getSingleValue($conn, $query, $userId = null) {
    $st = oci_parse($conn, $query);
    if ($userId !== null) { oci_bind_by_name($st, ':id', $userId); }
    
    if (!@oci_execute($st, OCI_DEFAULT)) {
        oci_free_statement($st);
        return 0;
    }
    
    $row = oci_fetch_assoc($st);
    oci_free_statement($st);
    
    if (!$row) return 0;
    $val = array_values($row)[0];
    return $val !== null ? $val : 0;
}

if ($role === 'gérant') {
    $kpis["Chiffre d'Affaires (Mois)"] = getSingleValue($conn, "SELECT SUM(montant_ca) FROM Chiffre_affaire WHERE EXTRACT(MONTH FROM date_ca) = EXTRACT(MONTH FROM SYSDATE)") . ' €';
    $kpis["Revenus Parrainages (Total)"] = getSingleValue($conn, "SELECT SUM(contribution) FROM Parrainage") . ' €';
    $kpis["Masse Salariale"] = getSingleValue($conn, "SELECT SUM(salaire_personnel) FROM Personnel") . ' €';
    $kpis["Coût Maintenance (Total)"] = getSingleValue($conn, "SELECT SUM(cout_reparation) FROM Reparation") . ' €';
    $kpis["Base Visiteurs"] = getSingleValue($conn, "SELECT COUNT(*) FROM Visiteur");
    $kpis["Effectif Actif"] = getSingleValue($conn, "SELECT COUNT(*) FROM Personnel");

} elseif ($role === 'veterinaire' || $role === 'vétérinaire') {
    $kpis["Soins Aujourd'hui"] = getSingleValue($conn, "SELECT COUNT(*) FROM Soins WHERE TRUNC(date_soin) = TRUNC(SYSDATE)");
    $kpis["Chirurgies (Mois)"] = getSingleValue($conn, "SELECT COUNT(*) FROM Soins WHERE type_soin = 'Chirurgie' AND EXTRACT(MONTH FROM date_soin) = EXTRACT(MONTH FROM SYSDATE)");
    $kpis["Animaux sans contrôle"] = getSingleValue($conn, "SELECT COUNT(*) FROM Animaux WHERE id_animaux NOT IN (SELECT id_animaux FROM Soins WHERE type_soin = 'Controle')");
    
    $esp = getSingleValue($conn, "SELECT MAX(e.nom_espece) FROM Especes e JOIN Animaux a ON e.id_espece = a.id_espece JOIN Soins s ON a.id_animaux = s.id_animaux GROUP BY e.id_espece ORDER BY COUNT(*) DESC FETCH FIRST 1 ROWS ONLY");
    $kpis["Espèce la plus soignée"] = $esp ?: 'Aucune';
    $kpis["Volume de vaccinations"] = getSingleValue($conn, "SELECT COUNT(*) FROM Soins WHERE type_soin = 'Vaccination'");
    $kpis["Animaux à surveiller (Total)"] = getSingleValue($conn, "SELECT COUNT(*) FROM Animaux");

} elseif ($role === 'soignant') {
    $kpis["Rations distribuées ce jour"] = getSingleValue($conn, "SELECT SUM(dose_journaliere_alimentation) FROM Alimentation WHERE TRUNC(date_alimentation) = TRUNC(SYSDATE)") . ' kg';
    $kpis["Animaux sous ma responsabilité"] = getSingleValue($conn, "SELECT COUNT(*) FROM Animaux WHERE id_personnel = :id", $userId);
    $kpis["Taux d'animaux nourris (Jour)"] = getSingleValue($conn, "SELECT COUNT(DISTINCT id_animaux) FROM Alimentation WHERE TRUNC(date_alimentation) = TRUNC(SYSDATE)");
    $kpis["Naissances historiques"] = getSingleValue($conn, "SELECT COUNT(*) FROM Est_mere_de");
    $kpis["Cohabitations actives"] = getSingleValue($conn, "SELECT COUNT(*) FROM Cohabite");
    $kpis["Régimes diététiques gérés"] = getSingleValue($conn, "SELECT COUNT(DISTINCT regime_alimentaire_animal) FROM Animaux");

} elseif ($role === 'technique') {
    $kpis["Total interventions"] = getSingleValue($conn, "SELECT COUNT(*) FROM Reparation");
    $kpis["Coût global réparations"] = getSingleValue($conn, "SELECT SUM(cout_reparation) FROM Reparation") . ' €';
    $kpis["Travaux délégués"] = getSingleValue($conn, "SELECT COUNT(*) FROM Prestataire_Reparation");
    $kpis["Prestataires référencés"] = getSingleValue($conn, "SELECT COUNT(*) FROM Prestataire");
    $kpis["Surface totale entretenue"] = getSingleValue($conn, "SELECT SUM(surface_enclo) FROM Enclos") . ' m²';
    $kpis["Nombre d'enclos gérés"] = getSingleValue($conn, "SELECT COUNT(*) FROM Enclos");

} elseif ($role === 'boutique') {
    $kpis["CA des boutiques du jour"] = getSingleValue($conn, "SELECT SUM(montant_ca) FROM Chiffre_affaire WHERE TRUNC(date_ca) = TRUNC(SYSDATE)") . ' €';
    $kpis["Fichier Visiteurs"] = getSingleValue($conn, "SELECT COUNT(*) FROM Visiteur");
    $kpis["Points de ventes"] = getSingleValue($conn, "SELECT COUNT(*) FROM Boutique");
    $kpis["Parrainages vendus"] = getSingleValue($conn, "SELECT COUNT(*) FROM Parrainage");
    $kpis["Effectifs en boutique"] = getSingleValue($conn, "SELECT COUNT(DISTINCT id_personnel) FROM Personnel_Boutique");
    $kpis["Offres au catalogue"] = getSingleValue($conn, "SELECT COUNT(*) FROM Prestation");
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
    
    <div>
        <h2>Vos informations (<?php echo htmlspecialchars($employee['TYPE_PERSONNEL']); ?>)</h2>
        <ul style="margin:0;">
            <li><strong>ID :</strong> <?php echo htmlspecialchars($employee['ID_PERSONNEL']); ?> - <?php echo htmlspecialchars($employee['PRENOM_PERSONNEL'] . ' ' . $employee['NOM_PERSONNEL']); ?></li>
            <li><strong>Salaire :</strong> <?php echo htmlspecialchars($employee['SALAIRE_PERSONNEL']); ?> €</li>
        </ul>
        <br>
        <a href="logout.php" style="margin-right: 15px;">Se déconnecter</a>
        <a href="home/profil/mot-de-passe.php" style="margin-right: 15px;">Mon Mot de passe</a>
        <a href="home/recherche.php" style="font-weight: bold; color: #2196F3;">Recherche Globale</a>
    </div>

    <h2>Vos Tableaux de Bord (KPIs)</h2>
    <div class="kpi-container">
        <?php foreach ($kpis as $titre => $valeur): ?>
            <div class="kpi-card">
                <h4><?php echo htmlspecialchars($titre); ?></h4>
                <p><?php echo $valeur !== '' ? htmlspecialchars($valeur) : '0'; ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>Vos Liens :</h2>
    <nav>
        <ul>
            <?php if (hasAccess('/public_html/home/animaux/index.php') || hasAccess('/public_html/home/especes/index.php') || hasAccess('/public_html/home/soins/index.php') || hasAccess('/public_html/home/alimentation/index.php')): ?>
            <li><h3>Animaux et Espèces</h3>
                <ul>
                    <?php if (hasAccess('/public_html/home/animaux/index.php')): ?><li><a href="home/animaux/index.php">Liste des Animaux</a></li><?php endif; ?>
                    <?php if (hasAccess('/public_html/home/especes/index.php')): ?><li><a href="home/especes/index.php">Liste des Espèces</a></li><?php endif; ?>
                    <?php if (hasAccess('/public_html/home/soins/index.php')): ?><li><a href="home/soins/index.php">Dossiers de Soins</a></li><?php endif; ?>
                    <?php if (hasAccess('/public_html/home/alimentation/index.php')): ?><li><a href="home/alimentation/index.php">Alimentation et Rations</a></li><?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>

            <?php if (hasAccess('/public_html/home/zones/index.php') || hasAccess('/public_html/home/enclos/index.php') || hasAccess('/public_html/home/reparations/index.php') || hasAccess('/public_html/home/particularites/index.php')): ?>
            <li><h3>Infrastructures</h3>
                <ul>
                    <?php if (hasAccess('/public_html/home/zones/index.php')): ?><li><a href="home/zones/index.php">Zones du Zoo</a></li><?php endif; ?>
                    <?php if (hasAccess('/public_html/home/enclos/index.php')): ?><li><a href="home/enclos/index.php">Liste des Enclos</a></li><?php endif; ?>
                    <?php if (hasAccess('/public_html/home/reparations/index.php')): ?><li><a href="home/reparations/index.php">Registre des Réparations</a></li><?php endif; ?>
                    <?php if (hasAccess('/public_html/home/particularites/index.php')): ?><li><a href="home/particularites/index.php">Particularités (Dictionnaire)</a></li><?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>

            <?php if (hasAccess('/public_html/home/personnel/index.php') || hasAccess('/public_html/home/prestataires/index.php')): ?>
            <li><h3>Personnel et Intervenants</h3>
                <ul>
                    <?php if (hasAccess('/public_html/home/personnel/index.php')): ?><li><a href="home/personnel/index.php">Membres du Personnel</a></li><?php endif; ?>
                    <?php if (hasAccess('/public_html/home/prestataires/index.php')): ?><li><a href="home/prestataires/index.php">Liste des Prestataires</a></li><?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>

            <?php if (hasAccess('/public_html/home/visiteurs/index.php') || hasAccess('/public_html/home/boutiques/index.php') || hasAccess('/public_html/home/prestations/index.php') || hasAccess('/public_html/home/parrainages/index.php')): ?>
            <li><h3>Boutique et Visiteurs</h3>
                <ul>
                    <?php if (hasAccess('/public_html/home/visiteurs/index.php')): ?><li><a href="home/visiteurs/index.php">Registre des Visiteurs</a></li><?php endif; ?>
                    <?php if (hasAccess('/public_html/home/boutiques/index.php')): ?><li><a href="home/boutiques/index.php">Liste des Boutiques</a></li><?php endif; ?>
                    <?php if (hasAccess('/public_html/home/prestations/index.php')): ?><li><a href="home/prestations/index.php">Prestations (Billet, Pass)</a></li><?php endif; ?>
                    <?php if (hasAccess('/public_html/home/parrainages/index.php')): ?><li><a href="home/parrainages/index.php">Parrainages d'animaux</a></li><?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</body>
</html>
