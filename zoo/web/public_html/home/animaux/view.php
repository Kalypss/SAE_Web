<?php
require_once __DIR__ . '/../../../backend/Auth.php';
// Vérification RBAC automatique
Auth::checkAccess();
require_once __DIR__ . '/../../../config/database.php';

$role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';
$id_animal = $_GET['id'] ?? null;

if (!$id_animal) {
    header("Location: /public_html/home/animaux/index.php");
    exit;
}

// 1. Infos générales de l'animal
$sql = "SELECT a.id_animaux, a.nom_animal, TO_CHAR(a.dob_animal, 'DD/MM/YYYY') as dob_format, a.dob_animal, a.poids_animal, 
               a.regime_alimentaire_animal, a.rfid_animal, a.id_personnel,
               e.id_espece, e.nomu_espece, e.est_menace, 
               enc.id_enclo, z.nom_zone, 
               p.nom_personnel, p.prenom_personnel,
               FLOOR(MONTHS_BETWEEN(SYSDATE, a.dob_animal) / 12) AS age_annees,
               MOD(FLOOR(MONTHS_BETWEEN(SYSDATE, a.dob_animal)), 12) AS age_mois
        FROM Animaux a
        JOIN Especes e ON a.id_espece = e.id_espece
        JOIN Enclos enc ON a.id_enclo = enc.id_enclo
        JOIN Zone z ON enc.id_zone = z.id_zone
        JOIN Personnel p ON a.id_personnel = p.id_personnel
        WHERE a.id_animaux = :id_animal";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id_animal', $id_animal);
oci_execute($stmt, OCI_DEFAULT);
$animal = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$animal) {
    die("Animal introuvable ou vous n'avez pas les droits.");
}

// Vérification de sécurité Soignant : il ne peut voir que si c'est SES animaux
if ($role === 'soignant' && (string)$animal['ID_PERSONNEL'] !== (string)$user_id) {
    header("Location: /public_html/errors/403.php");
    exit;
}

// Fonction pour récupérer requetes avec binding id_animal
function fetchMultiple($conn, $sql, $id) {
    $st = oci_parse($conn, $sql);
    oci_bind_by_name($st, ':id', $id);
    oci_execute($st, OCI_DEFAULT);
    $res = [];
    while ($row = oci_fetch_assoc($st)) { $res[] = $row; }
    oci_free_statement($st);
    return $res;
}

// 2. Parents : Père
$pere = fetchMultiple($conn, "SELECT p.id_animaux, p.nom_animal FROM Est_pere_de e JOIN Animaux p ON e.id_parent = p.id_animaux WHERE e.id_enfant = :id", $id_animal);
// 2. Parents : Mère
$mere = fetchMultiple($conn, "SELECT p.id_animaux, p.nom_animal FROM Est_mere_de e JOIN Animaux p ON e.id_parent = p.id_animaux WHERE e.id_enfant = :id", $id_animal);

// 3. Enfants (Père ou Mère)
$enfants_pere = fetchMultiple($conn, "SELECT enf.id_animaux, enf.nom_animal FROM Est_pere_de e JOIN Animaux enf ON e.id_enfant = enf.id_animaux WHERE e.id_parent = :id", $id_animal);
$enfants_mere = fetchMultiple($conn, "SELECT enf.id_animaux, enf.nom_animal FROM Est_mere_de e JOIN Animaux enf ON e.id_enfant = enf.id_animaux WHERE e.id_parent = :id", $id_animal);
$enfants = array_merge($enfants_pere, $enfants_mere);

// 4. Historique Soins (5 derniers) Oracle 12c+
$soins = fetchMultiple($conn, "
    SELECT s.id_soin, TO_CHAR(s.date_soin, 'DD/MM/YYYY') as date_format, s.type_soin, s.description_soin, p.prenom_personnel, p.nom_personnel 
    FROM Soins s 
    JOIN Personnel p ON s.id_personnel = p.id_personnel 
    WHERE s.id_animaux = :id 
    ORDER BY s.date_soin DESC 
    FETCH FIRST 5 ROWS ONLY", $id_animal);

// 5. Historique Alimentaire (5 derniers)
$alimentation = fetchMultiple($conn, "
    SELECT TO_CHAR(a.date_alimentation, 'DD/MM/YYYY') as date_format, a.dose_journaliere_alimentation, p.prenom_personnel, p.nom_personnel 
    FROM Alimentation a 
    JOIN Personnel p ON a.id_personnel = p.id_personnel 
    WHERE a.id_animaux = :id 
    ORDER BY a.date_alimentation DESC 
    FETCH FIRST 5 ROWS ONLY", $id_animal);

// 6. Parrainages
$parrainages = fetchMultiple($conn, "
    SELECT v.id_visiteur, v.nom_visiteur, v.prenom_visiteur, p.niveau, p.contribution, TO_CHAR(p.date_debut, 'DD/MM/YYYY') as date_format 
    FROM Parrainage p 
    JOIN Visiteur v ON p.id_visiteur = v.id_visiteur 
    WHERE p.id_animaux = :id 
    ORDER BY p.date_debut DESC", $id_animal);

function getRegimeColor($regime) {
    $colors = ['carnivore' => 'red', 'vegetarien' => 'green', 'omnivore' => 'orange', 'insectivore' => 'saddlebrown', 'filtreur' => 'blue'];
    return $colors[strtolower($regime)] ?? 'black';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche Animal - <?php echo htmlspecialchars($animal['NOM_ANIMAL'] ?? 'Sans Nom'); ?></title>
</head>
<body>
    <a href="/public_html/home/animaux/index.php">← Retour à la liste des animaux</a>
    
    <div style="display: flex; align-items: flex-start; margin-top: 20px;">
        <!-- Colonne Gauche : Photo (Placeholder) et Infos Principales -->
        <div style="margin-right: 40px; text-align: center;">
            <!-- Placeholder -->
            <div style="width: 200px; height: 200px; background-color: #333; color: white; display: flex; align-items: center; justify-content: center; font-size: 24px; border-radius: 8px;">
                Photo
            </div>
            
            <h1 style="margin: 10px 0 5px 0;">
                <?php echo (!empty(trim($animal['NOM_ANIMAL']))) ? htmlspecialchars($animal['NOM_ANIMAL']) : '—'; ?>
            </h1>
            
            <!-- RFID Copiable -->
            <div style="margin-bottom: 20px;">
                <strong>RFID:</strong> 
                <span style="font-family: monospace; border: 1px solid #ccc; padding: 2px 6px; cursor: pointer; background: #eee;" 
                      onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($animal['RFID_ANIMAL']); ?>'); alert('RFID copié !');" title="Cliquer pour copier">
                    <?php echo htmlspecialchars($animal['RFID_ANIMAL']); ?>
                </span>
            </div>

            <!-- Régime -->
            <div>
                <span style="color: white; background-color: <?php echo getRegimeColor($animal['REGIME_ALIMENTAIRE_ANIMAL']); ?>; padding: 5px 10px; border-radius: 5px; font-weight: bold;">
                    <?php echo htmlspecialchars(ucfirst($animal['REGIME_ALIMENTAIRE_ANIMAL'])); ?>
                </span>
            </div>
        </div>

        <!-- Colonne Droite : Métadonnées -->
        <div style="flex: 1;">
            <h2>Méta-données</h2>
            <ul>
                <li><strong>Date de Naissance :</strong> <?php echo htmlspecialchars($animal['DOB_FORMAT']); ?> 
                    (Âge: <?php echo $animal['AGE_ANNEES']; ?> ans et <?php echo $animal['AGE_MOIS']; ?> mois)</li>
                <li><strong>Poids :</strong> <?php echo htmlspecialchars($animal['POIDS_ANIMAL']); ?> kg</li>
                
                <li><strong>Espèce :</strong> 
                    <a href="/public_html/home/especes/view.php?id=<?php echo urlencode($animal['ID_ESPECE']); ?>">
                        <?php echo htmlspecialchars($animal['NOMU_ESPECE']); ?>
                        <?php if ($animal['EST_MENACE'] == 1): ?>
                            🚨 (Menacée)
                        <?php endif; ?>
                    </a>
                </li>
                
                <li><strong>Enclos :</strong> 
                    <a href="/public_html/home/enclos/view.php?id=<?php echo urlencode($animal['ID_ENCLO']); ?>">
                        Enclos n°<?php echo htmlspecialchars($animal['ID_ENCLO']); ?> (<?php echo htmlspecialchars($animal['NOM_ZONE'] ?? ''); ?>)
                    </a>
                </li>
                
                <?php if ($role !== 'soignant'): ?>
                    <li><strong>Soignant Responsable :</strong> 
                        <a href="/public_html/home/personnel/view.php?id=<?php echo urlencode($animal['ID_PERSONNEL']); ?>">
                            <?php echo htmlspecialchars($animal['PRENOM_PERSONNEL'] . ' ' . $animal['NOM_PERSONNEL']); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Parenté -->
            <h2>Parenté</h2>
            <ul>
                <li><strong>Père :</strong>
                    <?php if (count($pere) > 0): ?>
                        <a href="view.php?id=<?php echo urlencode($pere[0]['ID_ANIMAUX']); ?>"><?php echo htmlspecialchars($pere[0]['NOM_ANIMAL'] ?? 'ND'); ?></a>
                    <?php else: ?>
                        Inconnu
                    <?php endif; ?>
                </li>
                <li><strong>Mère :</strong>
                    <?php if (count($mere) > 0): ?>
                        <a href="view.php?id=<?php echo urlencode($mere[0]['ID_ANIMAUX']); ?>"><?php echo htmlspecialchars($mere[0]['NOM_ANIMAL'] ?? 'ND'); ?></a>
                    <?php else: ?>
                        Inconnue
                    <?php endif; ?>
                </li>
                <li><strong>Enfants :</strong>
                    <?php if (count($enfants) > 0): ?>
                        <ul>
                        <?php foreach($enfants as $enf): ?>
                            <li><a href="view.php?id=<?php echo urlencode($enf['ID_ANIMAUX']); ?>"><?php echo htmlspecialchars($enf['NOM_ANIMAL'] ?? 'ND'); ?></a></li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        Aucun
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>

    <!-- Séparateur -->
    <hr style="margin: 40px 0;">

    <div style="display: flex; gap: 20px;">
        <!-- Historique Alimentaire -->
        <div style="flex: 1;">
            <h3>Historique Alimentaire (5 derniers)</h3>
            <table border="1" cellpadding="5" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Dose (kg)</th>
                        <th>Soignant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($alimentation) > 0): ?>
                        <?php foreach($alimentation as $alim): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($alim['DATE_FORMAT']); ?></td>
                                <td><?php echo htmlspecialchars($alim['DOSE_JOURNALIERE_ALIMENTATION']); ?></td>
                                <td><?php echo htmlspecialchars($alim['PRENOM_PERSONNEL'] . ' ' . $alim['NOM_PERSONNEL']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3">Aucune donnée alimentaire.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Historique des Soins -->
        <div style="flex: 1;">
            <h3>Historique des Soins (5 derniers)</h3>
            <table border="1" cellpadding="5" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Vétérinaire</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($soins) > 0): ?>
                        <?php foreach($soins as $soin): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($soin['DATE_FORMAT']); ?></td>
                                <td>
                                    <a href="../soins/view.php?id=<?php echo urlencode($soin['ID_SOIN']); ?>">
                                        <?php echo htmlspecialchars($soin['TYPE_SOIN']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($soin['PRENOM_PERSONNEL'] . ' ' . $soin['NOM_PERSONNEL']); ?></td>
                                <td><?php echo htmlspecialchars($soin['DESCRIPTION_SOIN']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">Aucun historique de soins.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Parrains -->
    <hr style="margin: 40px 0;">
    <h3>Parrainages Actifs / Passés</h3>
    <table border="1" cellpadding="5" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Nom du Visiteur</th>
                <th>Niveau</th>
                <th>Contribution (€)</th>
                <th>Date de début</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($parrainages) > 0): ?>
                <?php foreach($parrainages as $parrain): ?>
                    <tr>
                        <td>
                            <a href="/public_html/visiteurs/view.php?id=<?php echo urlencode($parrain['ID_VISITEUR']); ?>">
                                <?php echo htmlspecialchars($parrain['PRENOM_VISITEUR'] . ' ' . $parrain['NOM_VISITEUR']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars(ucfirst($parrain['NIVEAU'])); ?></td>
                        <td><?php echo htmlspecialchars($parrain['CONTRIBUTION']); ?> €</td>
                        <td><?php echo htmlspecialchars($parrain['DATE_FORMAT']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">Aucun parrainage enregistré.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>