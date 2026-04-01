-- Requete numero 1 : Lister tous les animaux avec leur espece et enclos
SELECT a.id_animaux, a.nom_animal, e.nomU_espece, a.poids_animal, a.regime_alimentaire_animal, enc.id_enclo, z.nom_zone
FROM Animaux a
JOIN Especes e ON a.id_espece = e.id_espece
JOIN Enclos enc ON a.id_enclo = enc.id_enclo
JOIN Zone z ON enc.id_zone = z.id_zone
ORDER BY a.id_animaux;

-- Requete numero 2 : Compter le nombre d'animaux par zone
SELECT z.nom_zone, COUNT(a.id_animaux) AS nb_animaux
FROM Zone z
JOIN Enclos enc ON z.id_zone = enc.id_zone
JOIN Animaux a ON enc.id_enclo = a.id_enclo
GROUP BY z.nom_zone
ORDER BY nb_animaux DESC;

-- Requete numero 3 : Lister les especes menacees et le nombre d'animaux concernes
SELECT e.nomU_espece, e.nomL_espece, COUNT(a.id_animaux) AS nb_animaux
FROM Especes e
JOIN Animaux a ON e.id_espece = a.id_espece
WHERE e.est_menace = 1
GROUP BY e.id_espece, e.nomU_espece, e.nomL_espece
ORDER BY nb_animaux DESC;

-- Requete numero 4 : Trouver le soignant responsable de chaque animal
SELECT a.nom_animal, e.nomU_espece, p.prenom_personnel, p.nom_personnel, p.type_personnel
FROM Animaux a
JOIN Personnel p ON a.id_personnel = p.id_personnel
JOIN Especes e ON a.id_espece = e.id_espece
ORDER BY p.nom_personnel, a.nom_animal;

-- Requete numero 5 : Verifier les specialisations du personnel
SELECT p.prenom_personnel, p.nom_personnel, e.nomU_espece
FROM Etre_specialiste_de es
JOIN Personnel p ON es.id_personnel = p.id_personnel
JOIN Especes e ON es.id_espece = e.id_espece
ORDER BY p.nom_personnel, e.nomU_espece;

-- Requete numero 6 : Lister les soins effectues sur les animaux menaces
SELECT a.nom_animal, e.nomU_espece, s.type_soin, s.description_soin, s.date_soin, p.prenom_personnel
FROM Soins s
JOIN Animaux a ON s.id_animaux = a.id_animaux
JOIN Especes e ON a.id_espece = e.id_espece
JOIN Personnel p ON s.id_personnel = p.id_personnel
WHERE e.est_menace = 1
ORDER BY s.date_soin;

-- Requete numero 7 : Chiffre d'affaires total par boutique
SELECT b.id_boutique, b.type_boutique, z.nom_zone, SUM(ca.montant_ca) AS ca_total
FROM Boutique b
JOIN Zone z ON b.id_zone = z.id_zone
JOIN Chiffre_affaire ca ON b.id_boutique = ca.id_boutique
GROUP BY b.id_boutique, b.type_boutique, z.nom_zone
ORDER BY ca_total DESC;

-- Requete numero 8 : Evolution mensuelle du CA total du zoo
SELECT TO_CHAR(ca.date_ca, 'YYYY-MM') AS mois, SUM(ca.montant_ca) AS ca_mensuel
FROM Chiffre_affaire ca
GROUP BY TO_CHAR(ca.date_ca, 'YYYY-MM')
ORDER BY mois;

-- Requete numero 9 : Parrainages par niveau avec montant total
SELECT niveau, COUNT(*) AS nb_parrainages, SUM(contribution) AS total_contributions
FROM Parrainage
GROUP BY niveau
ORDER BY total_contributions DESC;

-- Requete numero 10 : Prestations accessibles par niveau de parrainage
SELECT v.nom_visiteur, v.prenom_visiteur, p.niveau, pr.description_prestation
FROM Parrainage p
JOIN Visiteur v ON p.id_visiteur = v.id_visiteur
JOIN Attribuer att ON p.id_parrainage = att.id_parrainage
JOIN Prestation pr ON att.id_prestation = pr.id_prestation
ORDER BY p.niveau DESC, v.nom_visiteur;

-- Requete numero 11 : Animaux qui n'ont recu aucun soin
SELECT a.nom_animal, e.nomU_espece, a.dob_animal
FROM Animaux a
JOIN Especes e ON a.id_espece = e.id_espece
LEFT JOIN Soins s ON a.id_animaux = s.id_animaux
WHERE s.id_soin IS NULL
ORDER BY a.nom_animal;

-- Requete numero 12 : Hierarchie du personnel (chef et subordonnes)
SELECT
    sub.prenom_personnel AS employe_prenom,
    sub.nom_personnel AS employe_nom,
    sub.type_personnel,
    chef.prenom_personnel AS chef_prenom,
    chef.nom_personnel AS chef_nom
FROM Personnel sub
LEFT JOIN Personnel chef ON sub.id_personnel_chef = chef.id_personnel
ORDER BY chef.nom_personnel, sub.nom_personnel;

-- Requete numero 13 : Historique de carriere complet d'un employe
SELECT p.prenom_personnel, p.nom_personnel, h.type_poste, h.date_debut, h.date_fin, h.salaire
FROM Historique_emploi h
JOIN Personnel p ON h.id_personnel = p.id_personnel
WHERE p.nom_personnel = 'Dupont'
ORDER BY h.date_debut;

-- Requete numero 14 : Especes pouvant cohabiter
SELECT e1.nomU_espece AS espece_1, e2.nomU_espece AS espece_2
FROM Cohabitation c
JOIN Especes e1 ON c.id_espece = e1.id_espece
JOIN Especes e2 ON c.id_espece_1 = e2.id_espece
WHERE c.id_espece < c.id_espece_1
ORDER BY e1.nomU_espece;

-- Requete numero 15 : Relations parentales (mere et pere)
SELECT 'Mere' AS relation, parent.nom_animal AS parent_nom, enfant.nom_animal AS enfant_nom
FROM Est_mere_de em
JOIN Animaux parent ON em.id_parent = parent.id_animaux
JOIN Animaux enfant ON em.id_enfant = enfant.id_animaux
UNION ALL
SELECT 'Pere' AS relation, parent.nom_animal AS parent_nom, enfant.nom_animal AS enfant_nom
FROM Est_pere_de ep
JOIN Animaux parent ON ep.id_parent = parent.id_animaux
JOIN Animaux enfant ON ep.id_enfant = enfant.id_animaux
ORDER BY relation, parent_nom;

-- Requete numero 16 : Reparations par prestataire avec details
SELECT pr.nom_prestataire, pr.specialite_prestataire, r.nature_reparation, r.date_reparation, z.nom_zone
FROM Reparation r
JOIN Prestataire pr ON r.id_prestataire = pr.id_prestataire
JOIN Enclos enc ON r.id_enclo = enc.id_enclo
JOIN Zone z ON enc.id_zone = z.id_zone
ORDER BY r.date_reparation;

-- Requete numero 17 : Particularites de chaque enclos
SELECT enc.id_enclo, z.nom_zone, LISTAGG(par.nom_particularite, ', ') WITHIN GROUP (ORDER BY par.nom_particularite) AS particularites
FROM Enclos enc
JOIN Zone z ON enc.id_zone = z.id_zone
JOIN Posseder_particularite pp ON enc.id_enclo = pp.id_enclo
JOIN Particularite par ON pp.id_particularite = par.id_particularite
GROUP BY enc.id_enclo, z.nom_zone
ORDER BY enc.id_enclo;

-- Requete numero 18 : Personnel affecte aux boutiques et leurs responsabilites
SELECT p.prenom_personnel, p.nom_personnel, b.type_boutique, z.nom_zone, pb.est_responsable
FROM Personnel_Boutique pb
JOIN Personnel p ON pb.id_personnel = p.id_personnel
JOIN Boutique b ON pb.id_boutique = b.id_boutique
JOIN Zone z ON b.id_zone = z.id_zone
ORDER BY pb.est_responsable DESC, p.nom_personnel;

-- Requete numero 19 : Dose alimentaire totale par zone
SELECT z.nom_zone, SUM(al.dose_journaliere_alimentation) AS dose_totale
FROM Alimentation al
JOIN Animaux a ON al.id_animaux = a.id_animaux
JOIN Enclos enc ON a.id_enclo = enc.id_enclo
JOIN Zone z ON enc.id_zone = z.id_zone
WHERE al.date_alimentation = TO_DATE('2026-03-01', 'YYYY-MM-DD')
GROUP BY z.nom_zone
ORDER BY dose_totale DESC;

-- Requete numero 20 : Visiteurs parrainant des animaux menaces
SELECT v.nom_visiteur, v.prenom_visiteur, a.nom_animal, e.nomU_espece, p.niveau, p.contribution
FROM Parrainage p
JOIN Visiteur v ON p.id_visiteur = v.id_visiteur
JOIN Animaux a ON p.id_animaux = a.id_animaux
JOIN Especes e ON a.id_espece = e.id_espece
WHERE e.est_menace = 1
ORDER BY p.contribution DESC;

-- Requete numero 21 : Animaux les plus lourds du zoo (top 5)
SELECT a.nom_animal, e.nomU_espece, a.poids_animal, z.nom_zone
FROM Animaux a
JOIN Especes e ON a.id_espece = e.id_espece
JOIN Enclos enc ON a.id_enclo = enc.id_enclo
JOIN Zone z ON enc.id_zone = z.id_zone
ORDER BY a.poids_animal DESC
FETCH FIRST 5 ROWS ONLY;

-- Requete numero 22 : Personnel sans zone affectee (personnel boutique/admin)
SELECT p.prenom_personnel, p.nom_personnel, p.type_personnel, p.salaire_personnel
FROM Personnel p
WHERE p.id_zone IS NULL
ORDER BY p.nom_personnel;

-- Requete numero 23 : Masse salariale totale par type de personnel
SELECT type_personnel, COUNT(*) AS nb_employes, SUM(salaire_personnel) AS masse_salariale, AVG(salaire_personnel) AS salaire_moyen
FROM Personnel
GROUP BY type_personnel
ORDER BY masse_salariale DESC;

-- Requete numero 24 : Animaux nes en 2024 ou apres (jeunes animaux)
SELECT a.nom_animal, e.nomU_espece, a.dob_animal, a.poids_animal
FROM Animaux a
JOIN Especes e ON a.id_espece = e.id_espece
WHERE a.dob_animal >= TO_DATE('2024-01-01', 'YYYY-MM-DD')
ORDER BY a.dob_animal DESC;

-- Requete numero 25 : Soignants qui s'occupent d'animaux hors de leur zone
SELECT p.prenom_personnel, p.nom_personnel, p.id_zone AS zone_personnel, z.nom_zone AS zone_enclos, a.nom_animal
FROM Animaux a
JOIN Personnel p ON a.id_personnel = p.id_personnel
JOIN Enclos enc ON a.id_enclo = enc.id_enclo
JOIN Zone z ON enc.id_zone = z.id_zone
WHERE p.id_zone IS NOT NULL AND p.id_zone <> enc.id_zone
ORDER BY p.nom_personnel;

-- Requete numero 26 : Remplacants disponibles pour chaque employe
SELECT
    p.prenom_personnel AS employe,
    p.nom_personnel AS nom_employe,
    p.type_personnel,
    r.prenom_personnel AS remplacant,
    r.nom_personnel AS nom_remplacant
FROM Personnel p
JOIN Personnel r ON p.id_personnel_remplacant = r.id_personnel
ORDER BY p.nom_personnel;

-- Requete numero 27 : Nombre de soins par type et par mois
SELECT TO_CHAR(s.date_soin, 'YYYY-MM') AS mois, s.type_soin, COUNT(*) AS nb_soins
FROM Soins s
GROUP BY TO_CHAR(s.date_soin, 'YYYY-MM'), s.type_soin
ORDER BY mois, s.type_soin;

-- Requete numero 28 : Enclos avec le plus d'animaux
SELECT enc.id_enclo, z.nom_zone, enc.surface_enclo, COUNT(a.id_animaux) AS nb_animaux,
       ROUND(enc.surface_enclo / COUNT(a.id_animaux), 2) AS surface_par_animal
FROM Enclos enc
JOIN Zone z ON enc.id_zone = z.id_zone
JOIN Animaux a ON enc.id_enclo = a.id_enclo
GROUP BY enc.id_enclo, z.nom_zone, enc.surface_enclo
ORDER BY nb_animaux DESC;

-- Requete numero 29 : Chiffre d'affaires du meilleur mois (ete)
SELECT TO_CHAR(ca.date_ca, 'YYYY-MM') AS mois, SUM(ca.montant_ca) AS ca_total
FROM Chiffre_affaire ca
GROUP BY TO_CHAR(ca.date_ca, 'YYYY-MM')
ORDER BY ca_total DESC
FETCH FIRST 3 ROWS ONLY;

-- Requete numero 30 : Verification integrite - Animaux sans alimentation enregistree
SELECT a.nom_animal, e.nomU_espece
FROM Animaux a
JOIN Especes e ON a.id_espece = e.id_espece
LEFT JOIN Alimentation al ON a.id_animaux = al.id_animaux
WHERE al.id_alimentation IS NULL;

-- Requete numero 31 : Sous-requete avec IN
SELECT a.nom_animal, e.nomU_espece, p.nom_personnel
FROM Animaux a
JOIN Especes e ON a.id_espece = e.id_espece
JOIN Personnel p ON a.id_personnel = p.id_personnel
WHERE a.id_personnel IN (
    SELECT es.id_personnel
    FROM Etre_specialiste_de es
    WHERE es.id_espece = a.id_espece
);

-- Requete numero 32 : Sous-requete avec NOT IN
SELECT a.nom_animal, e.nomU_espece, p.nom_personnel
FROM Animaux a
JOIN Especes e ON a.id_espece = e.id_espece
JOIN Personnel p ON a.id_personnel = p.id_personnel
WHERE a.id_personnel NOT IN (
    SELECT es.id_personnel
    FROM Etre_specialiste_de es
    WHERE es.id_espece = a.id_espece
);

-- Requete numero 33 : EXISTS - Especes qui ont au moins un animal dans le zoo
SELECT e.nomU_espece, e.nomL_espece
FROM Especes e
WHERE EXISTS (
    SELECT 1
    FROM Animaux a
    WHERE a.id_espece = e.id_espece
);

-- Requete numero 34 : NOT EXISTS - Especes sans aucun animal dans le zoo
SELECT e.nomU_espece, e.nomL_espece, e.est_menace
FROM Especes e
WHERE NOT EXISTS (
    SELECT 1
    FROM Animaux a
    WHERE a.id_espece = e.id_espece
)
ORDER BY e.nomU_espece;

-- Requete numero 35 : ALL - Soignant dont le salaire est superieur a tous les salaires boutique
SELECT p.prenom_personnel, p.nom_personnel, p.salaire_personnel
FROM Personnel p
WHERE p.type_personnel = 'soignant'
AND p.salaire_personnel > ALL (
    SELECT p2.salaire_personnel
    FROM Personnel p2
    WHERE p2.type_personnel = 'boutique'
);

-- Requete numero 36 : ANY - Personnel technique gagnant plus qu'au moins un veterinaire
SELECT p.prenom_personnel, p.nom_personnel, p.salaire_personnel
FROM Personnel p
WHERE p.type_personnel = 'technique'
AND p.salaire_personnel > ANY (
    SELECT p2.salaire_personnel
    FROM Personnel p2
    WHERE p2.type_personnel = 'veterinaire'
);

-- Requete numero 37 : DIVISION par denombrement
SELECT p.prenom_personnel, p.nom_personnel
FROM Etre_specialiste_de es
JOIN Personnel p ON es.id_personnel = p.id_personnel
WHERE es.id_espece IN (13, 14, 15, 16)
GROUP BY p.id_personnel, p.prenom_personnel, p.nom_personnel
HAVING COUNT(DISTINCT es.id_espece) = (
    SELECT COUNT(*)
    FROM Especes
    WHERE id_espece IN (13, 14, 15, 16)
);

-- Requete numero 38 : DIVISION par double negation (NOT EXISTS imbriques)
SELECT p.prenom_personnel, p.nom_personnel
FROM Personnel p
WHERE NOT EXISTS (
    SELECT e.id_espece
    FROM Especes e
    WHERE e.id_espece IN (1, 2, 3, 4)
    AND NOT EXISTS (
        SELECT 1
        FROM Etre_specialiste_de es
        WHERE es.id_personnel = p.id_personnel
        AND es.id_espece = e.id_espece
    )
);

-- Requete numero 39 : DIVISION par operations ensemblistes (MINUS)
SELECT DISTINCT p.prenom_personnel, p.nom_personnel
FROM Personnel p
JOIN Etre_specialiste_de es ON p.id_personnel = es.id_personnel
WHERE NOT EXISTS (
    SELECT id_espece FROM Especes WHERE id_espece IN (9, 10, 11)
    MINUS
    SELECT es2.id_espece FROM Etre_specialiste_de es2 WHERE es2.id_personnel = p.id_personnel
);

-- Requete numero 40 : UNION - Tous les soignants ayant soit nourri soit soigne Punch
SELECT DISTINCT p.prenom_personnel, p.nom_personnel, 'Soin' AS type_intervention
FROM Soins s
JOIN Personnel p ON s.id_personnel = p.id_personnel
WHERE s.id_animaux = 1
UNION
SELECT DISTINCT p.prenom_personnel, p.nom_personnel, 'Alimentation' AS type_intervention
FROM Alimentation al
JOIN Personnel p ON al.id_personnel = p.id_personnel
WHERE al.id_animaux = 1;

-- Requete numero 41 : INTERSECT - Visiteurs parrainant zone Singe ET zone Requin
SELECT v.nom_visiteur, v.prenom_visiteur
FROM Parrainage par
JOIN Visiteur v ON par.id_visiteur = v.id_visiteur
JOIN Animaux a ON par.id_animaux = a.id_animaux
JOIN Enclos enc ON a.id_enclo = enc.id_enclo
JOIN Zone z ON enc.id_zone = z.id_zone
WHERE z.nom_zone = 'Singe'
INTERSECT
SELECT v.nom_visiteur, v.prenom_visiteur
FROM Parrainage par
JOIN Visiteur v ON par.id_visiteur = v.id_visiteur
JOIN Animaux a ON par.id_animaux = a.id_animaux
JOIN Enclos enc ON a.id_enclo = enc.id_enclo
JOIN Zone z ON enc.id_zone = z.id_zone
WHERE z.nom_zone = 'Requin';

-- Requete numero 42 : LIKE - Rechercher des animaux dont le nom commence par M
SELECT a.nom_animal, e.nomU_espece
FROM Animaux a
JOIN Especes e ON a.id_espece = e.id_espece
WHERE a.nom_animal LIKE 'M%';

-- Requete numero 43 : LIKE avance - Especes dont le nom latin contient 'us'
SELECT nomU_espece, nomL_espece
FROM Especes
WHERE nomL_espece LIKE '%us%'
ORDER BY nomL_espece;

-- Requete numero 44 : BETWEEN - Animaux dont le poids est entre 1 et 50 kg
SELECT a.nom_animal, e.nomU_espece, a.poids_animal
FROM Animaux a
JOIN Especes e ON a.id_espece = e.id_espece
WHERE a.poids_animal BETWEEN 1 AND 50
ORDER BY a.poids_animal DESC;

-- Requete numero 45 : BETWEEN sur dates - Soins effectues au premier trimestre 2026
SELECT a.nom_animal, s.type_soin, s.description_soin, s.date_soin
FROM Soins s
JOIN Animaux a ON s.id_animaux = a.id_animaux
WHERE s.date_soin BETWEEN TO_DATE('2026-01-01', 'YYYY-MM-DD') AND TO_DATE('2026-03-31', 'YYYY-MM-DD')
ORDER BY s.date_soin;

-- Requete numero 46 : Jointure externe gauche (LEFT OUTER JOIN)
SELECT z.nom_zone, b.id_boutique, b.type_boutique
FROM Zone z
LEFT OUTER JOIN Boutique b ON z.id_zone = b.id_zone
ORDER BY z.nom_zone;

-- Requete numero 47 : Jointure externe droite (RIGHT OUTER JOIN)
SELECT pr.nom_prestataire, pr.specialite_prestataire, r.nature_reparation, r.date_reparation
FROM Reparation r
RIGHT OUTER JOIN Prestataire pr ON r.id_prestataire = pr.id_prestataire
ORDER BY pr.nom_prestataire;

-- Requete numero 48 : Sous-requete dans le SELECT - Nombre d'animaux par soignant
SELECT p.prenom_personnel, p.nom_personnel,
    (SELECT COUNT(*) FROM Animaux a WHERE a.id_personnel = p.id_personnel) AS nb_animaux
FROM Personnel p
WHERE p.type_personnel IN ('soignant', 'veterinaire')
ORDER BY nb_animaux DESC;

-- Requete numero 49 : GROUP BY + HAVING - Zones avec plus de 5 animaux
SELECT z.nom_zone, COUNT(a.id_animaux) AS nb_animaux
FROM Zone z
JOIN Enclos enc ON z.id_zone = enc.id_zone
JOIN Animaux a ON enc.id_enclo = a.id_enclo
GROUP BY z.nom_zone
HAVING COUNT(a.id_animaux) > 5
ORDER BY nb_animaux DESC;

-- Requete numero 50 : HAVING avec SUM - Boutiques avec CA total superieur a 5000
SELECT b.type_boutique, z.nom_zone, SUM(ca.montant_ca) AS ca_total
FROM Boutique b
JOIN Zone z ON b.id_zone = z.id_zone
JOIN Chiffre_affaire ca ON b.id_boutique = ca.id_boutique
GROUP BY b.id_boutique, b.type_boutique, z.nom_zone
HAVING SUM(ca.montant_ca) > 5000
ORDER BY ca_total DESC;

-- Requete numero 51 : Vue - Fiche resumee de chaque animal
CREATE OR REPLACE VIEW vue_fiche_animal AS
SELECT a.id_animaux, a.nom_animal, e.nomU_espece, e.nomL_espece, e.est_menace,
       a.dob_animal, a.poids_animal, a.regime_alimentaire_animal, a.rfid_animal,
       p.prenom_personnel AS soignant_prenom, p.nom_personnel AS soignant_nom,
       enc.id_enclo, z.nom_zone
FROM Animaux a
JOIN Especes e ON a.id_espece = e.id_espece
JOIN Personnel p ON a.id_personnel = p.id_personnel
JOIN Enclos enc ON a.id_enclo = enc.id_enclo
JOIN Zone z ON enc.id_zone = z.id_zone;

SELECT * FROM vue_fiche_animal ORDER BY nom_zone, nom_animal;

-- Requete numero 52 : Vue - Tableau de bord boutiques
CREATE OR REPLACE VIEW vue_boutique_ca AS
SELECT b.id_boutique, b.type_boutique, z.nom_zone,
       resp.prenom_personnel AS responsable_prenom, resp.nom_personnel AS responsable_nom,
       SUM(ca.montant_ca) AS ca_total
FROM Boutique b
JOIN Zone z ON b.id_zone = z.id_zone
LEFT JOIN Chiffre_affaire ca ON b.id_boutique = ca.id_boutique
LEFT JOIN Personnel_Boutique pb ON b.id_boutique = pb.id_boutique AND pb.est_responsable = 1
LEFT JOIN Personnel resp ON pb.id_personnel = resp.id_personnel
GROUP BY b.id_boutique, b.type_boutique, z.nom_zone, resp.prenom_personnel, resp.nom_personnel;

SELECT * FROM vue_boutique_ca ORDER BY ca_total DESC;

-- Requete numero 53 : Auto-jointure - Paires soignant/remplacant avec salaires
SELECT
    tit.prenom_personnel AS titulaire,
    tit.salaire_personnel AS salaire_titulaire,
    remp.prenom_personnel AS remplacant,
    remp.salaire_personnel AS salaire_remplacant,
    tit.salaire_personnel - remp.salaire_personnel AS difference
FROM Personnel tit
JOIN Personnel remp ON tit.id_personnel_remplacant = remp.id_personnel
ORDER BY difference DESC;

-- Requete numero 54 : Sous-requete synchronisee complexe
SELECT a.nom_animal, e.nomU_espece, a.poids_animal, z.nom_zone
FROM Animaux a
JOIN Especes e ON a.id_espece = e.id_espece
JOIN Enclos enc ON a.id_enclo = enc.id_enclo
JOIN Zone z ON enc.id_zone = z.id_zone
WHERE a.poids_animal = (
    SELECT MAX(a2.poids_animal)
    FROM Animaux a2
    JOIN Enclos enc2 ON a2.id_enclo = enc2.id_enclo
    WHERE enc2.id_zone = enc.id_zone
)
ORDER BY a.poids_animal DESC;

-- Requete numero 55 : DISTINCT + COUNT + GROUP BY + HAVING + ORDER BY combines
SELECT p.prenom_personnel, p.nom_personnel, COUNT(DISTINCT s.type_soin) AS nb_types_soins
FROM Soins s
JOIN Personnel p ON s.id_personnel = p.id_personnel
GROUP BY p.id_personnel, p.prenom_personnel, p.nom_personnel
HAVING COUNT(DISTINCT s.type_soin) >= 2
ORDER BY nb_types_soins DESC;
