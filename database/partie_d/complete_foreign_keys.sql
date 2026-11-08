-- Partie D : reconciliation non destructive apres installation des tables B/C.
-- Ce script peut etre rejoue. Il n'ajoute une cle etrangere que si les deux
-- tables concernees existent dans le search_path courant.

DO $$
BEGIN
    IF to_regclass('anomalie_pointage') IS NOT NULL
       AND to_regclass('planning_travail') IS NOT NULL
       AND NOT EXISTS (
           SELECT 1 FROM pg_constraint
           WHERE conname = 'fk_anomalie_planning'
             AND conrelid = 'anomalie_pointage'::regclass
       ) THEN
        ALTER TABLE anomalie_pointage
            ADD CONSTRAINT fk_anomalie_planning
            FOREIGN KEY (planning_id) REFERENCES planning_travail(id)
            ON DELETE SET NULL NOT VALID;
    END IF;

    IF to_regclass('anomalie_pointage') IS NOT NULL
       AND to_regclass('pointage') IS NOT NULL
       AND NOT EXISTS (
           SELECT 1 FROM pg_constraint
           WHERE conname = 'fk_anomalie_pointage'
             AND conrelid = 'anomalie_pointage'::regclass
       ) THEN
        ALTER TABLE anomalie_pointage
            ADD CONSTRAINT fk_anomalie_pointage
            FOREIGN KEY (pointage_id) REFERENCES pointage(id)
            ON DELETE SET NULL NOT VALID;
    END IF;

    IF to_regclass('absence') IS NOT NULL
       AND to_regclass('demande_conge') IS NOT NULL
       AND NOT EXISTS (
           SELECT 1 FROM pg_constraint
           WHERE conname = 'fk_absence_demande_conge'
             AND conrelid = 'absence'::regclass
       ) THEN
        ALTER TABLE absence
            ADD CONSTRAINT fk_absence_demande_conge
            FOREIGN KEY (demande_conge_id) REFERENCES demande_conge(id)
            ON DELETE SET NULL NOT VALID;
    END IF;
END $$;
