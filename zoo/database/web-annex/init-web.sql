CREATE TABLE Droits (
    id_droit NUMBER PRIMARY KEY NOT NULL,
    id_personnel NUMBER NOT NULL REFERENCES Personnel(id_personnel) ON DELETE CASCADE,
    peut_gerer_animaux NUMBER(1) DEFAULT 0 NOT NULL CHECK (peut_gerer_animaux IN (0, 1)),
    peut_gerer_soins NUMBER(1) DEFAULT 0 NOT NULL CHECK (peut_gerer_soins IN (0, 1)),
    peut_gerer_alimentation NUMBER(1) DEFAULT 0 NOT NULL CHECK (peut_gerer_alimentation IN (0, 1)),
    peut_gerer_enclos NUMBER(1) DEFAULT 0 NOT NULL CHECK (peut_gerer_enclos IN (0, 1)),
    peut_gerer_personnel NUMBER(1) DEFAULT 0 NOT NULL CHECK (peut_gerer_personnel IN (0, 1)),
    peut_gerer_boutiques NUMBER(1) DEFAULT 0 NOT NULL CHECK (peut_gerer_boutiques IN (0, 1)),
    peut_gerer_parrainages NUMBER(1) DEFAULT 0 NOT NULL CHECK (peut_gerer_parrainages IN (0, 1)),
    peut_consulter_ca NUMBER(1) DEFAULT 0 NOT NULL CHECK (peut_consulter_ca IN (0, 1)),
    peut_gerer_reparations NUMBER(1) DEFAULT 0 NOT NULL CHECK (peut_gerer_reparations IN (0, 1)),
    est_admin NUMBER(1) DEFAULT 0 NOT NULL CHECK (est_admin IN (0, 1)),
    CONSTRAINT uq_droits_personnel UNIQUE (id_personnel)
);

CREATE TABLE Logs (
    id_log NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY NOT NULL,
    id_personnel NUMBER REFERENCES Personnel(id_personnel) ON DELETE SET NULL,
    action_log VARCHAR2(50) NOT NULL CHECK (action_log IN ('connexion', 'deconnexion', 'creation', 'modification', 'suppression', 'consultation', 'changement_mdp', 'tentative_echec')),
    table_concernee VARCHAR2(100),
    id_enregistrement NUMBER,
    detail_log VARCHAR2(500),
    date_log TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    adresse_ip VARCHAR2(45)
);
