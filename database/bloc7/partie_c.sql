-- =====================================================================
-- SIRH — Bloc 7, partie C : demandes de congé, validations et soldes
-- Cible : PostgreSQL. À exécuter après db_SIRH_fusion.sql ET db_SIRH_donnees_test.sql.
--
-- Ce script crée le schéma nécessaire à la partie C et le contrat minimal
-- d'absence consommé à la validation finale (à compléter par la partie D).
-- type_conge est créé ici comme référentiel minimal partagé : la partie D
-- pourra le compléter avec les paramètres de calcul propres à chaque type.
--
-- Décision structurelle nécessaire : employe.manager_id permet de déterminer
-- les salariés dont un Manager peut traiter les demandes. La colonne est
-- nullable pour les employés sans Manager défini et pour compatibilité avec
-- les lignes déjà présentes dans db_SIRH_donnees_test.sql.
-- Les règles de soumission, décompte, chevauchement et validation sont
-- appliquées par les services du Bloc 7; les tranches d'acquisition sont
-- générées par le service à chaque mois complet de contrat.
-- =====================================================================

BEGIN;

-- Un employé est aussi son propre compte (employe.email / mot_de_passe).
-- Les rôles multiples permettent à un Manager/RH d'utiliser aussi le portail salarié.
INSERT INTO role (nom_role, description)
VALUES ('Salarie', 'Accès au portail personnel de congés')
ON CONFLICT (nom_role) DO UPDATE SET description = EXCLUDED.description;

CREATE TABLE IF NOT EXISTS employe_role (
    employe_id BIGINT NOT NULL REFERENCES employe(id) ON DELETE CASCADE,
    role_id BIGINT NOT NULL REFERENCES role(id) ON DELETE RESTRICT,
    attribue_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (employe_id, role_id)
);
INSERT INTO employe_role (employe_id, role_id)
SELECT id, role_id FROM employe WHERE role_id IS NOT NULL
ON CONFLICT DO NOTHING;
INSERT INTO employe_role (employe_id, role_id)
SELECT e.id, r.id FROM employe e CROSS JOIN role r
WHERE e.actif = TRUE AND r.nom_role = 'Salarie'
ON CONFLICT DO NOTHING;

-- Responsable hiérarchique direct. Une relation historique pourra remplacer
-- cette colonne si le projet doit conserver les changements d'équipe.
ALTER TABLE employe
    ADD COLUMN IF NOT EXISTS manager_id BIGINT
    REFERENCES employe(id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_employe_manager_id
    ON employe(manager_id);

-- Calendrier paramétrable des jours fériés, utilisé pour le décompte en jours.
CREATE TABLE IF NOT EXISTS jour_ferie (
    date_ferie DATE PRIMARY KEY,
    libelle VARCHAR(150) NOT NULL,
    actif BOOLEAN NOT NULL DEFAULT TRUE
);

-- Référentiel minimal partagé avec la partie D.
CREATE TABLE IF NOT EXISTS type_conge (
    id                  BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    code                VARCHAR(40) NOT NULL UNIQUE,
    libelle             VARCHAR(100) NOT NULL,
    description         TEXT,
    actif               BOOLEAN NOT NULL DEFAULT TRUE,
    unite_decompte      VARCHAR(20) NOT NULL DEFAULT 'jours_ouvres',
    justificatif_requis BOOLEAN,
    cree_le             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_type_conge_unite
        CHECK (unite_decompte = 'jours_ouvres')
);

-- Historique des droits attribués/acquis au salarié par type et période.
-- Les quantités restent positives ; les régularisations nécessitant un débit
-- doivent être cadrées avant de définir leur représentation.
CREATE TABLE IF NOT EXISTS droit_conge (
    id              BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    employe_id      BIGINT NOT NULL REFERENCES employe(id) ON DELETE RESTRICT,
    contrat_id      BIGINT NOT NULL,
    type_conge_id   BIGINT NOT NULL REFERENCES type_conge(id) ON DELETE RESTRICT,
    date_acquisition DATE NOT NULL,
    date_expiration DATE NOT NULL,
    quantite        NUMERIC(8,2) NOT NULL,
    nature          VARCHAR(20) NOT NULL DEFAULT 'acquisition',
    origine         VARCHAR(100),
    commentaire     TEXT,
    attribue_le     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    attribue_par    BIGINT REFERENCES employe(id) ON DELETE SET NULL,
    CONSTRAINT fk_droit_conge_contrat_employe FOREIGN KEY (contrat_id, employe_id)
        REFERENCES contrat(id, employe_id) ON DELETE RESTRICT,
    CONSTRAINT chk_droit_conge_expiration CHECK (date_expiration > date_acquisition),
    CONSTRAINT chk_droit_conge_delai CHECK (
        nature <> 'acquisition' OR date_expiration = (date_acquisition + INTERVAL '3 years')::DATE
    ),
    CONSTRAINT chk_droit_conge_quantite CHECK (quantite > 0),
    CONSTRAINT chk_droit_conge_nature
        CHECK (nature IN ('acquisition', 'regularisation'))
);

CREATE INDEX IF NOT EXISTS idx_droit_conge_employe_type_validite
    ON droit_conge(employe_id, type_conge_id, contrat_id, date_expiration);
CREATE UNIQUE INDEX IF NOT EXISTS uq_droit_conge_acquisition_mensuelle
    ON droit_conge(contrat_id, type_conge_id, date_acquisition) WHERE nature = 'acquisition';

-- Synthèse de solde par salarié, type et contrat. Les colonnes composantes
-- sont maintenues à partir des tranches non expirées et des demandes réservées.
CREATE TABLE IF NOT EXISTS solde_conge (
    id                  BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    employe_id          BIGINT NOT NULL REFERENCES employe(id) ON DELETE RESTRICT,
    contrat_id          BIGINT NOT NULL,
    type_conge_id       BIGINT NOT NULL REFERENCES type_conge(id) ON DELETE RESTRICT,
    -- Un solde distinct par contrat matérialise la remise à zéro au renouvellement.
    quantite_acquise    NUMERIC(8,2) NOT NULL DEFAULT 0,
    quantite_consommee  NUMERIC(8,2) NOT NULL DEFAULT 0,
    quantite_reservee   NUMERIC(8,2) NOT NULL DEFAULT 0,
    quantite_disponible NUMERIC(8,2) GENERATED ALWAYS AS
        (quantite_acquise - quantite_consommee - quantite_reservee) STORED,
    calcule_le          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_solde_conge_contrat_employe FOREIGN KEY (contrat_id, employe_id)
        REFERENCES contrat(id, employe_id) ON DELETE RESTRICT,
    CONSTRAINT uq_solde_conge_contrat_type UNIQUE (contrat_id, type_conge_id),
    CONSTRAINT chk_solde_conge_quantites CHECK (
        quantite_acquise >= 0 AND quantite_consommee >= 0 AND quantite_reservee >= 0
    )
);

CREATE INDEX IF NOT EXISTS idx_solde_conge_employe_type
    ON solde_conge(employe_id, type_conge_id, contrat_id);

-- Demande du salarié. employe_id désigne le bénéficiaire ; contrat_id fixe le
-- contrat dont le solde est utilisé ; saisi_par_employe_id désigne le créateur.
CREATE TABLE IF NOT EXISTS demande_conge (
    id                    BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    reference             VARCHAR(30) NOT NULL UNIQUE,
    employe_id            BIGINT NOT NULL REFERENCES employe(id) ON DELETE RESTRICT,
    saisi_par_employe_id  BIGINT NOT NULL REFERENCES employe(id) ON DELETE RESTRICT,
    contrat_id            BIGINT NOT NULL,
    type_conge_id         BIGINT NOT NULL REFERENCES type_conge(id) ON DELETE RESTRICT,
    date_debut            DATE NOT NULL,
    date_fin              DATE NOT NULL,
    portion_debut         VARCHAR(20) NOT NULL DEFAULT 'journee',
    portion_fin           VARCHAR(20) NOT NULL DEFAULT 'journee',
    duree_demandee        NUMERIC(8,2) NOT NULL,
    unite_decompte        VARCHAR(20) NOT NULL DEFAULT 'jours_ouvres',
    statut                VARCHAR(30) NOT NULL DEFAULT 'brouillon',
    motif                 TEXT,
    cree_le               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    soumise_le            TIMESTAMP,
    cloturee_le           TIMESTAMP,
    CONSTRAINT chk_demande_conge_periode CHECK (date_fin >= date_debut),
    CONSTRAINT fk_demande_conge_contrat_employe FOREIGN KEY (contrat_id, employe_id)
        REFERENCES contrat(id, employe_id) ON DELETE RESTRICT,
    CONSTRAINT chk_demande_conge_portions CHECK (
        portion_debut IN ('journee', 'matin', 'apres_midi')
        AND portion_fin IN ('journee', 'matin', 'apres_midi')
        AND (date_debut <> date_fin OR portion_debut = portion_fin)
    ),
    CONSTRAINT chk_demande_conge_duree CHECK (duree_demandee > 0),
    CONSTRAINT chk_demande_conge_unite CHECK (unite_decompte = 'jours_ouvres'),
    CONSTRAINT chk_demande_conge_statut CHECK (statut IN (
        'brouillon', 'soumise', 'en_attente_manager', 'en_attente_rh',
        'a_corriger', 'approuvee', 'refusee', 'retiree', 'annulee'
    )),
    CONSTRAINT chk_demande_conge_dates_etat CHECK (
        statut IN ('brouillon', 'retiree') OR soumise_le IS NOT NULL
    )
);

-- Un brouillon peut être retiré avant sa première soumission : il n'a alors
-- pas de date soumise_le. Remplacer également la contrainte sur les bases
-- existantes, où la table avait déjà été créée par une version précédente.
ALTER TABLE demande_conge DROP CONSTRAINT IF EXISTS chk_demande_conge_dates_etat;
ALTER TABLE demande_conge ADD CONSTRAINT chk_demande_conge_dates_etat
    CHECK (statut IN ('brouillon', 'retiree') OR soumise_le IS NOT NULL);

ALTER TABLE demande_conge ADD COLUMN IF NOT EXISTS portion_debut VARCHAR(20) NOT NULL DEFAULT 'journee';
ALTER TABLE demande_conge ADD COLUMN IF NOT EXISTS portion_fin VARCHAR(20) NOT NULL DEFAULT 'journee';

-- Détail de réservation puis consommation par tranche mensuelle (allocation FIFO).
CREATE TABLE IF NOT EXISTS allocation_droit_conge (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    demande_conge_id BIGINT NOT NULL REFERENCES demande_conge(id) ON DELETE RESTRICT,
    droit_conge_id BIGINT NOT NULL REFERENCES droit_conge(id) ON DELETE RESTRICT,
    numero_tentative SMALLINT NOT NULL DEFAULT 1,
    quantite NUMERIC(8,2) NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'reservee',
    cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_allocation_droit_quantite CHECK (quantite > 0),
    CONSTRAINT chk_allocation_droit_tentative CHECK (numero_tentative > 0),
    CONSTRAINT chk_allocation_droit_statut CHECK (statut IN ('reservee','consommee','liberee'))
);
CREATE INDEX IF NOT EXISTS idx_allocation_droit_demande
    ON allocation_droit_conge(demande_conge_id, numero_tentative, statut);
CREATE INDEX IF NOT EXISTS idx_allocation_droit_tranche
    ON allocation_droit_conge(droit_conge_id, statut);

CREATE INDEX IF NOT EXISTS idx_demande_conge_employe_statut_dates
    ON demande_conge(employe_id, statut, date_debut, date_fin);
CREATE INDEX IF NOT EXISTS idx_demande_conge_type_statut
    ON demande_conge(type_conge_id, statut);

-- Historique append-only des décisions (Manager/RH). Les retours en correction
-- et nouvelles soumissions sont distingués par numero_tentative.
CREATE TABLE IF NOT EXISTS validation_demande_conge (
    id                    BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    demande_conge_id      BIGINT NOT NULL REFERENCES demande_conge(id) ON DELETE RESTRICT,
    validateur_employe_id BIGINT NOT NULL REFERENCES employe(id) ON DELETE RESTRICT,
    etape                 VARCHAR(20) NOT NULL,
    numero_tentative      SMALLINT NOT NULL DEFAULT 1,
    decision              VARCHAR(20) NOT NULL,
    commentaire           TEXT,
    decide_le             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_validation_conge_tentative
        UNIQUE (demande_conge_id, etape, numero_tentative),
    CONSTRAINT chk_validation_conge_etape CHECK (etape IN ('manager', 'rh')),
    CONSTRAINT chk_validation_conge_tentative CHECK (numero_tentative > 0),
    CONSTRAINT chk_validation_conge_decision
        CHECK (decision IN ('approuvee', 'refusee', 'a_corriger'))
);

CREATE INDEX IF NOT EXISTS idx_validation_conge_validateur
    ON validation_demande_conge(validateur_employe_id, etape, decide_le);
CREATE INDEX IF NOT EXISTS idx_validation_conge_demande
    ON validation_demande_conge(demande_conge_id, decide_le);

-- Journal d'audit des retraits salariés et annulations RH après approbation.
CREATE TABLE IF NOT EXISTS annulation_demande_conge (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    demande_conge_id BIGINT NOT NULL REFERENCES demande_conge(id) ON DELETE RESTRICT,
    annule_par_employe_id BIGINT NOT NULL REFERENCES employe(id) ON DELETE RESTRICT,
    motif TEXT NOT NULL,
    annule_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Métadonnées des pièces jointes aux demandes ; les fichiers eux-mêmes doivent
-- être conservés hors du répertoire public. L'obligation dépend de chaque type
-- de congé et est appliquée par le service lors de la soumission.
CREATE TABLE IF NOT EXISTS justificatif_conge (
    id                    BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    demande_conge_id      BIGINT NOT NULL REFERENCES demande_conge(id) ON DELETE RESTRICT,
    ajoute_par_employe_id BIGINT NOT NULL REFERENCES employe(id) ON DELETE RESTRICT,
    nom_fichier           VARCHAR(255) NOT NULL,
    chemin_stockage       VARCHAR(500) NOT NULL,
    type_mime             VARCHAR(100),
    taille_octets         BIGINT,
    ajoute_le             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_justificatif_conge_taille
        CHECK (taille_octets IS NULL OR taille_octets >= 0)
);

CREATE INDEX IF NOT EXISTS idx_justificatif_conge_demande
    ON justificatif_conge(demande_conge_id);

-- Contrat minimal d'intégration avec la partie D : une absence est créée à
-- l'approbation du Manager ou de la RH et reste liée à sa demande source.
CREATE TABLE IF NOT EXISTS absence (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    demande_conge_id BIGINT NOT NULL UNIQUE REFERENCES demande_conge(id) ON DELETE RESTRICT,
    employe_id BIGINT NOT NULL REFERENCES employe(id) ON DELETE RESTRICT,
    contrat_id BIGINT NOT NULL,
    type_conge_id BIGINT NOT NULL REFERENCES type_conge(id) ON DELETE RESTRICT,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    duree NUMERIC(8,2) NOT NULL,
    unite_decompte VARCHAR(20) NOT NULL DEFAULT 'jours_ouvres',
    statut VARCHAR(20) NOT NULL DEFAULT 'planifiee',
    cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_absence_conge_periode CHECK (date_fin >= date_debut),
    CONSTRAINT fk_absence_conge_contrat_employe FOREIGN KEY (contrat_id, employe_id)
        REFERENCES contrat(id, employe_id) ON DELETE RESTRICT,
    CONSTRAINT chk_absence_conge_duree CHECK (duree > 0),
    CONSTRAINT chk_absence_conge_unite CHECK (unite_decompte = 'jours_ouvres'),
    CONSTRAINT chk_absence_conge_statut CHECK (statut IN ('planifiee', 'en_cours', 'terminee', 'annulee'))
);
CREATE INDEX IF NOT EXISTS idx_absence_conge_employe_dates
    ON absence(employe_id, date_debut, date_fin);

-- Notifications internes : soumission vers le Manager/RH, puis décision vers le salarié.
CREATE TABLE IF NOT EXISTS notification_employe (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    destinataire_employe_id BIGINT NOT NULL REFERENCES employe(id) ON DELETE CASCADE,
    demande_conge_id BIGINT REFERENCES demande_conge(id) ON DELETE CASCADE,
    code VARCHAR(40) NOT NULL,
    titre VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    lue_le TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_notification_employe_non_lues
    ON notification_employe(destinataire_employe_id, cree_le DESC) WHERE lue_le IS NULL;

COMMENT ON COLUMN employe.manager_id IS
    'Responsable hiérarchique direct utilisé pour le périmètre de validation des congés.';
COMMENT ON TABLE solde_conge IS
    'Synthèse maintenue par le service ; quantite_disponible est calculée à partir de ses composantes.';
COMMENT ON TABLE type_conge IS
    'Référentiel minimal partagé avec la partie D du Bloc 7.';
COMMENT ON TABLE absence IS
    'Contrat minimal d’intégration : alimenté à l’approbation par le valideur routé, à compléter par la partie D.';
COMMENT ON TABLE employe_role IS
    'Rôles multiples d’un compte employé, dont Salarié pour le portail personnel.';
COMMENT ON TABLE notification_employe IS
    'Notifications internes de congés; la remise par email éventuelle est un canal distinct.';

COMMIT;
