-- =====================================================================
-- SIRH — Données de démonstration pour le Bloc 7, partie C
-- À exécuter après db_SIRH_fusion.sql, db_SIRH_donnees_test.sql,
-- puis database/bloc7/partie_c.sql, sur une base de test.
-- Les identifiants employés correspondent au jeu de données test existant.
-- Les règles et quantités ci-dessous sont fictives.
-- =====================================================================

BEGIN;

-- Rattachements hiérarchiques de démonstration pour tester la file Manager.
UPDATE employe SET manager_id = 1 WHERE id IN (7, 10);
UPDATE employe SET manager_id = 5 WHERE id = 8;
UPDATE employe SET manager_id = 6 WHERE id IN (9, 11);

INSERT INTO type_conge
    (id, code, libelle, description, actif, unite_decompte, justificatif_requis)
OVERRIDING SYSTEM VALUE VALUES
    (1, 'ANNUEL', 'Congé annuel', 'Acquiert 2,5 jours ouvrés par mois complet.', TRUE, 'jours_ouvres', FALSE),
    (2, 'MALADIE', 'Congé maladie', 'Ne déduit pas le solde de congé annuel.', TRUE, 'jours_ouvres', FALSE),
    (3, 'EXCEPTIONNEL', 'Congé exceptionnel', 'Ne déduit pas le solde de congé annuel.', TRUE, 'jours_ouvres', FALSE);

INSERT INTO droit_conge
    (id, employe_id, contrat_id, type_conge_id, date_acquisition, date_expiration, quantite, nature, origine, attribue_par)
OVERRIDING SYSTEM VALUE VALUES
    (1, 7, 7,  1, '2026-02-01', '2029-02-01', 2.5, 'acquisition', 'Acquisition mensuelle', 2),
    (2, 7, 7,  1, '2026-03-01', '2029-03-01', 2.5, 'acquisition', 'Acquisition mensuelle', 2),
    (3, 7, 8,  1, '2026-05-01', '2029-05-01', 2.5, 'acquisition', 'Acquisition mensuelle', 2),
    (4, 7, 8,  1, '2026-06-01', '2029-06-01', 2.5, 'acquisition', 'Acquisition mensuelle', 2),
    (5, 7, 8,  1, '2026-07-01', '2029-07-01', 2.5, 'acquisition', 'Acquisition mensuelle', 2),
    (6, 7, 9,  1, '2026-09-01', '2029-09-01', 2.5, 'acquisition', 'Acquisition mensuelle', 2),
    (7, 7, 9,  1, '2026-10-01', '2029-10-01', 2.5, 'acquisition', 'Acquisition mensuelle', 2),
    (8, 7, 9,  1, '2026-11-01', '2029-11-01', 2.5, 'acquisition', 'Acquisition mensuelle', 2),
    (9, 8, 10, 1, '2026-10-01', '2029-10-01', 2.5, 'acquisition', 'Acquisition mensuelle', 2),
    (10,8, 10, 1, '2026-11-01', '2029-11-01', 2.5, 'acquisition', 'Acquisition mensuelle', 2),
    (11,11,14, 1, '2026-10-01', '2029-10-01', 2.5, 'acquisition', 'Acquisition mensuelle', 2),
    (12,11,14, 1, '2026-11-01', '2029-11-01', 2.5, 'acquisition', 'Acquisition mensuelle', 2);

INSERT INTO solde_conge
    (id, employe_id, contrat_id, type_conge_id,
     quantite_acquise, quantite_consommee, quantite_reservee)
OVERRIDING SYSTEM VALUE VALUES
    (1, 7, 7,  1, 5,   0, 0),
    (2, 7, 8,  1, 7.5, 0, 0),
    (3, 7, 9,  1, 7.5, 0, 5),
    (4, 8, 10, 1, 5,   2, 0),
    (5, 11,14, 1, 5,   0, 0);

INSERT INTO demande_conge
    (id, reference, employe_id, saisi_par_employe_id, contrat_id, type_conge_id,
     date_debut, date_fin, duree_demandee, unite_decompte, statut, motif, soumise_le)
OVERRIDING SYSTEM VALUE VALUES
    (1, 'CONG-2026-0001', 7, 7, 9, 1, '2026-11-16', '2026-11-20', 5, 'jours_ouvres', 'en_attente_manager',
     'Congé de démonstration en attente du Manager.', '2026-11-03 09:00'),
    (2, 'CONG-2026-0002', 8, 8, 10, 1, '2026-12-01', '2026-12-02', 2, 'jours_ouvres', 'approuvee',
     'Congé de démonstration approuvé.', '2026-10-20 10:00'),
    (3, 'CONG-2026-0003', 11, 11, 14, 1, '2026-12-21', '2026-12-22', 2, 'jours_ouvres', 'refusee',
     'Congé de démonstration refusé.', '2026-10-21 11:00'),
    (4, 'CONG-2026-0004', 10, 10, 12, 2, '2026-11-10', '2026-11-11', 2, 'jours_ouvres', 'brouillon',
     'Brouillon de démonstration.', NULL);

INSERT INTO allocation_droit_conge
    (id, demande_conge_id, droit_conge_id, numero_tentative, quantite, statut)
OVERRIDING SYSTEM VALUE VALUES
    (1, 1, 6, 1, 2.5, 'reservee'),
    (2, 1, 7, 1, 2.5, 'reservee'),
    (3, 2, 9, 1, 2, 'consommee');

-- Une demande déjà approuvée de la fixture possède l'absence correspondante.
INSERT INTO absence
    (demande_conge_id, employe_id, contrat_id, type_conge_id, date_debut, date_fin, duree, unite_decompte, statut)
VALUES
    (2, 8, 10, 1, '2026-12-01', '2026-12-02', 2, 'jours_ouvres', 'planifiee');

INSERT INTO validation_demande_conge
    (id, demande_conge_id, validateur_employe_id, etape, numero_tentative, decision, commentaire, decide_le)
OVERRIDING SYSTEM VALUE VALUES
    (1, 2, 5, 'manager', 1, 'approuvee', 'Demande approuvée par le Manager.', '2026-10-21 09:00'),
    (2, 3, 6, 'manager', 1, 'refusee',   'Demande refusée par le Manager.',   '2026-10-22 10:00');

INSERT INTO justificatif_conge
    (id, demande_conge_id, ajoute_par_employe_id, nom_fichier, chemin_stockage, type_mime, taille_octets)
OVERRIDING SYSTEM VALUE VALUES
    (1, 4, 2, 'certificat_demo.pdf', 'private/conges/demo/certificat_demo.pdf', 'application/pdf', 102400);

-- Recalage des séquences après les identifiants de démonstration explicites.
SELECT setval(pg_get_serial_sequence('type_conge', 'id'),
              COALESCE((SELECT MAX(id) FROM type_conge), 1), TRUE);
SELECT setval(pg_get_serial_sequence('droit_conge', 'id'),
              COALESCE((SELECT MAX(id) FROM droit_conge), 1), TRUE);
SELECT setval(pg_get_serial_sequence('solde_conge', 'id'),
              COALESCE((SELECT MAX(id) FROM solde_conge), 1), TRUE);
SELECT setval(pg_get_serial_sequence('demande_conge', 'id'),
              COALESCE((SELECT MAX(id) FROM demande_conge), 1), TRUE);
SELECT setval(pg_get_serial_sequence('allocation_droit_conge', 'id'),
              COALESCE((SELECT MAX(id) FROM allocation_droit_conge), 1), TRUE);
SELECT setval(pg_get_serial_sequence('validation_demande_conge', 'id'),
              COALESCE((SELECT MAX(id) FROM validation_demande_conge), 1), TRUE);
SELECT setval(pg_get_serial_sequence('justificatif_conge', 'id'),
              COALESCE((SELECT MAX(id) FROM justificatif_conge), 1), TRUE);

COMMIT;
