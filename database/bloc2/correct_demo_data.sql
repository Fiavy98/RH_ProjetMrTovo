-- Correction strictement réservée à une base PostgreSQL de démonstration/test.
-- Relire les lignes ciblées avant COMMIT. Le garde-fou refuse une base dont le
-- nom ne contient ni "demo", ni "test".
BEGIN;

DO $$
BEGIN
    IF current_database() !~* '(demo|test)' THEN
        RAISE EXCEPTION 'Base refusée : % ne ressemble pas à une base demo/test', current_database();
    END IF;
    IF to_regclass('public.status') IS NULL
       OR to_regclass('public.demande') IS NULL
       OR to_regclass('public.validation') IS NULL THEN
        RAISE EXCEPTION 'Tables Bloc 1 requises absentes';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM status WHERE code = 'DIRECTION_validee') THEN
        RAISE EXCEPTION 'Statut DIRECTION_validee absent';
    END IF;
END $$;

-- Seules les validations de démonstration 1..7 explicitement décrites comme
-- un accord Direction sont requalifiées. Une validation RH ordinaire reste RH.
UPDATE validation AS v
SET status_id = sd.id
FROM status AS sr, status AS sd
WHERE v.demande_id BETWEEN 1 AND 7
  AND v.status_id = sr.id
  AND sr.code = 'RH_validee'
  AND sd.code = 'DIRECTION_validee'
  AND lower(coalesce(v.commentaire, '')) LIKE '%accord de la direction%';

-- La demande n'est avancée qu'en présence de sa trace Direction cohérente.
UPDATE demande AS d
SET status_id = sd.id
FROM status AS current_status, status AS sd
WHERE d.id BETWEEN 1 AND 7
  AND d.status_id = current_status.id
  AND current_status.code = 'RH_validee'
  AND sd.code = 'DIRECTION_validee'
  AND EXISTS (
      SELECT 1
      FROM validation AS v
      JOIN status AS sv ON sv.id = v.status_id
      WHERE v.demande_id = d.id
        AND sv.code = 'DIRECTION_validee'
        AND lower(coalesce(v.commentaire, '')) LIKE '%accord de la direction%'
  );

-- Normalisation non ambiguë des types historiques.
UPDATE entretien
SET type_entretien = upper(type_entretien)
WHERE upper(type_entretien) IN ('RH', 'TECHNIQUE', 'FINAL', 'TEST_TECHNIQUE')
  AND type_entretien <> upper(type_entretien);

-- Un évaluateur historique constitue une preuve explicite de participation.
-- Aucun participant fictif n'est créé pour les entretiens sans évaluation.
INSERT INTO entretien_participant (entretien_id, employe_id, role_participant)
SELECT DISTINCT e.entretien_id, e.evaluateur_id, 'EVALUATEUR'
FROM evaluation_candidat AS e
JOIN entretien AS i ON i.id = e.entretien_id
JOIN employe AS emp ON emp.id = e.evaluateur_id
WHERE e.evaluateur_id IS NOT NULL
ON CONFLICT (entretien_id, employe_id) DO NOTHING;

-- Le statut administratif correct de `poste` n'est pas défini dans les
-- migrations partagées examinées. On refuse donc de transformer aveuglément
-- FINANCE_validee. Après arbitrage, remplacer POSTE_STATUT_A_CONFIRMER par le
-- code officiel et décommenter l'UPDATE.
-- UPDATE poste AS p
-- SET status_id = cible.id
-- FROM status AS actuel, status AS cible
-- WHERE p.status_id = actuel.id
--   AND actuel.code IN ('FINANCE_validee', 'FINANCE_refusee', 'FINANCE_validation')
--   AND cible.code = 'POSTE_STATUT_A_CONFIRMER';

-- Contrôle avant validation manuelle.
SELECT d.id, s.code AS statut_demande
FROM demande AS d JOIN status AS s ON s.id = d.status_id
WHERE d.id BETWEEN 1 AND 7 ORDER BY d.id;

SELECT v.id, v.demande_id, s.code AS statut_validation, v.commentaire
FROM validation AS v JOIN status AS s ON s.id = v.status_id
WHERE v.demande_id BETWEEN 1 AND 7 ORDER BY v.demande_id, v.id;

SELECT e.id AS entretien_id, count(ep.employe_id) AS participants
FROM entretien AS e
LEFT JOIN entretien_participant AS ep ON ep.entretien_id = e.id
GROUP BY e.id ORDER BY e.id;

-- Remplacer par ROLLBACK lors d'une simple prévisualisation.
COMMIT;
