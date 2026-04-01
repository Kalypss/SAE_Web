CREATE TABLE Especes (
    id_espece NUMBER PRIMARY KEY NOT NULL,
    nomU_espece VARCHAR2(250) NOT NULL,
    nomL_espece VARCHAR2(250) NOT NULL UNIQUE,
    est_menace NUMBER(1) DEFAULT 0 NOT NULL CHECK (est_menace IN (0, 1))
);

CREATE TABLE Zone (
    id_zone NUMBER PRIMARY KEY NOT NULL,
    nom_zone VARCHAR2(250) DEFAULT ' ' NOT NULL UNIQUE
);

CREATE TABLE Enclos (
    id_enclo NUMBER PRIMARY KEY NOT NULL,
    longitude_enclo NUMBER(15,2) NOT NULL,
    latitude_enclo NUMBER(15,2) NOT NULL,
    surface_enclo NUMBER NOT NULL CHECK (surface_enclo > 0),
    id_zone NUMBER NOT NULL REFERENCES Zone(id_zone)
);

CREATE TABLE Particularite (
    id_particularite NUMBER PRIMARY KEY NOT NULL,
    nom_particularite VARCHAR2(250) DEFAULT ' ' NOT NULL UNIQUE
);

CREATE TABLE Personnel (
    id_personnel NUMBER PRIMARY KEY NOT NULL,
    nom_personnel VARCHAR2(20) NOT NULL,
    prenom_personnel VARCHAR2(250) NOT NULL,
    pwd_personnel VARCHAR2(255) NOT NULL,
    salaire_personnel NUMBER(15,2) CHECK (salaire_personnel > 0),
    type_personnel VARCHAR2(250) CHECK (type_personnel IN ('veterinaire', 'soignant', 'boutique', 'technique','gérant')),
    date_entree_personnel DATE NOT NULL,
    id_personnel_remplacant NUMBER REFERENCES Personnel(id_personnel),
    id_zone NUMBER REFERENCES Zone(id_zone),
    id_personnel_chef NUMBER REFERENCES Personnel(id_personnel)
);

CREATE TABLE Visiteur (
    id_visiteur NUMBER PRIMARY KEY NOT NULL,
    nom_visiteur VARCHAR2(255) NOT NULL,
    prenom_visiteur VARCHAR2(255) NOT NULL,
    Email_visiteur VARCHAR2(255) NOT NULL UNIQUE
);

CREATE TABLE Prestation (
    id_prestation NUMBER PRIMARY KEY NOT NULL,
    description_prestation VARCHAR2(250),
    niveau_requis VARCHAR2(50) NOT NULL CHECK (niveau_requis IN ('bronze', 'argent', 'or'))
);

CREATE TABLE Boutique (
    id_boutique NUMBER PRIMARY KEY NOT NULL,
    type_boutique VARCHAR2(255) NOT NULL,
    id_zone NUMBER NOT NULL REFERENCES Zone(id_zone)
);

CREATE TABLE Chiffre_affaire (
    id_ca NUMBER PRIMARY KEY NOT NULL,
    date_ca DATE NOT NULL,
    montant_ca NUMBER(15,2) NOT NULL CHECK (montant_ca >= 0),
    id_boutique NUMBER NOT NULL REFERENCES Boutique(id_boutique)
);

CREATE TABLE Prestataire (
    id_prestataire NUMBER PRIMARY KEY NOT NULL,
    nom_prestataire VARCHAR2(255) NOT NULL,
    specialite_prestataire VARCHAR2(255) NOT NULL
);

CREATE TABLE Historique_emploi (
    id_historique NUMBER PRIMARY KEY NOT NULL,
    type_poste VARCHAR2(250) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE,
    salaire NUMBER(15,2) NOT NULL CHECK (salaire > 0),
    id_personnel NUMBER NOT NULL REFERENCES Personnel(id_personnel),
    CHECK (date_fin IS NULL OR date_fin >= date_debut)
);

CREATE TABLE Animaux (
    id_animaux NUMBER PRIMARY KEY NOT NULL,
    nom_animal VARCHAR2(250) DEFAULT ' ',
    dob_animal DATE NOT NULL,
    poids_animal NUMBER(15,2) NOT NULL CHECK (poids_animal > 0),
    regime_alimentaire_animal VARCHAR2(255) NOT NULL CHECK (regime_alimentaire_animal IN ('vegetarien', 'carnivore', 'insectivore', 'filtreur', 'omnivore')),
    rfid_animal VARCHAR2(250) NOT NULL UNIQUE,
    id_personnel NUMBER NOT NULL REFERENCES Personnel(id_personnel),
    id_enclo NUMBER NOT NULL REFERENCES Enclos(id_enclo),
    id_espece NUMBER NOT NULL REFERENCES Especes(id_espece)
);

CREATE TABLE Soins (
    id_soin NUMBER PRIMARY KEY NOT NULL,
    type_soin VARCHAR2(250) NOT NULL CHECK (type_soin IN ('Vaccination', 'Controle', 'Traitement', 'Chirurgie')),
    description_soin VARCHAR2(255) DEFAULT ' ' NOT NULL,
    date_soin DATE NOT NULL,
    id_personnel NUMBER NOT NULL REFERENCES Personnel(id_personnel),
    id_animaux NUMBER NOT NULL REFERENCES Animaux(id_animaux)
);

CREATE TABLE Alimentation (
    id_alimentation NUMBER PRIMARY KEY NOT NULL,
    dose_journaliere_alimentation NUMBER(15,2) NOT NULL CHECK (dose_journaliere_alimentation > 0),
    date_alimentation DATE NOT NULL,
    id_personnel NUMBER NOT NULL REFERENCES Personnel(id_personnel),
    id_animaux NUMBER NOT NULL REFERENCES Animaux(id_animaux)
);

CREATE TABLE Parrainage (
    id_parrainage NUMBER PRIMARY KEY NOT NULL,
    niveau VARCHAR2(50) DEFAULT 'rien' NOT NULL CHECK (niveau IN ('rien', 'bronze', 'argent', 'or')),
    contribution NUMBER(15,2) DEFAULT 0 NOT NULL CHECK (contribution >= 0),
    date_debut DATE NOT NULL,
    id_animaux NUMBER NOT NULL REFERENCES Animaux(id_animaux),
    id_visiteur NUMBER NOT NULL REFERENCES Visiteur(id_visiteur)
);

CREATE TABLE Reparation (
    id_reparation NUMBER PRIMARY KEY NOT NULL,
    nature_reparation VARCHAR2(250) DEFAULT ' ' NOT NULL,
    date_reparation DATE NOT NULL,
    id_prestataire NUMBER NOT NULL REFERENCES Prestataire(id_prestataire),
    id_personnel NUMBER NOT NULL REFERENCES Personnel(id_personnel),
    id_enclo NUMBER NOT NULL REFERENCES Enclos(id_enclo)
);

CREATE TABLE Cohabitation (
    id_espece NUMBER NOT NULL REFERENCES Especes(id_espece),
    id_espece_1 NUMBER NOT NULL REFERENCES Especes(id_espece),
    PRIMARY KEY(id_espece, id_espece_1)
);

CREATE TABLE Est_mere_de (
    id_parent NUMBER NOT NULL REFERENCES Animaux(id_animaux),
    id_enfant NUMBER NOT NULL REFERENCES Animaux(id_animaux),
    PRIMARY KEY(id_parent)
);

CREATE TABLE Posseder_particularite (
    id_enclo NUMBER NOT NULL REFERENCES Enclos(id_enclo),
    id_particularite NUMBER NOT NULL REFERENCES Particularite(id_particularite),
    PRIMARY KEY(id_enclo, id_particularite)
);

CREATE TABLE Etre_specialiste_de (
    id_espece NUMBER NOT NULL REFERENCES Especes(id_espece),
    id_personnel NUMBER NOT NULL REFERENCES Personnel(id_personnel),
    PRIMARY KEY(id_espece, id_personnel)
);

CREATE TABLE Personnel_Boutique (
    id_personnel NUMBER NOT NULL REFERENCES Personnel(id_personnel),
    id_boutique NUMBER NOT NULL REFERENCES Boutique(id_boutique),
    est_responsable NUMBER(1) DEFAULT 0 NOT NULL CHECK (est_responsable IN (0, 1)),
    PRIMARY KEY(id_personnel, id_boutique)
);

CREATE TABLE Est_pere_de (
    id_parent NUMBER NOT NULL REFERENCES Animaux(id_animaux),
    id_enfant NUMBER NOT NULL REFERENCES Animaux(id_animaux),
    PRIMARY KEY(id_parent)
);

CREATE TABLE Attribuer (
    id_parrainage NUMBER NOT NULL REFERENCES Parrainage(id_parrainage),
    id_prestation NUMBER NOT NULL REFERENCES Prestation(id_prestation),
    PRIMARY KEY(id_parrainage, id_prestation)
);
