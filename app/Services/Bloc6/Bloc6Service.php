<?php

namespace App\Services\Bloc6;

/** Catalogue de maquettes du Bloc 6. Aucune donnée n'est lue ou sauvegardée. */
class Bloc6Service
{
    public function screens(): array
    {
        return [
            'horaires' => [
                'title' => 'Horaires et régimes', 'subtitle' => 'Définition des horaires de travail',
                'description' => 'Référentiel des régimes, plages horaires et pauses applicables aux salariés.',
                'stats' => [['Régimes actifs', '04'], ['Horaires définis', '06'], ['Salariés concernés', '128']],
                'columns' => ['Régime', 'Début', 'Fin', 'Pause', 'Durée prévue'],
                'rows' => [['Temps plein', '08:00', '17:00', '12:00–13:00', '8 h / jour'], ['Temps aménagé', '08:30', '16:30', '12:00–13:00', '7 h / jour']],
                'fields' => [['Nouveau régime', 'Nom · jours travaillés · heures prévues · plages horaires']],
            ],
            'planning' => [
                'title' => 'Planning de travail', 'subtitle' => 'Affectation des horaires par salarié et période',
                'description' => 'Vue maquette pour préparer et consulter les horaires planifiés.',
                'stats' => [['Salariés planifiés', '121'], ['À planifier', '07'], ['Semaine affichée', '03–09 nov.']],
                'columns' => ['Salarié', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.'],
                'rows' => [['Rado Rakoto', '08:00–17:00', '08:00–17:00', '08:00–17:00', '08:00–17:00', '08:00–16:00'], ['Miora Andrian', '08:30–16:30', '08:30–16:30', 'Repos', '08:30–16:30', '08:30–16:30']],
            ],
            'pointages' => [
                'title' => 'Entrées et sorties', 'subtitle' => 'Journal des pointages et heures supplémentaires',
                'description' => 'Consultation des heures d’entrée et de sortie, retards et heures supplémentaires déclarées.',
                'stats' => [['Pointages du jour', '116'], ['Retards détectés', '05'], ['Heures supplémentaires', '18 h']],
                'columns' => ['Salarié', 'Date', 'Entrée', 'Sortie', 'Retard', 'Heures supplémentaires'],
                'rows' => [['Rado Rakoto', '03/11/2026', '08:07', '17:12', '7 min', '12 min'], ['Miora Andrian', '03/11/2026', '08:28', '16:30', 'Aucun', '—']],
                'fields' => [['Saisie de pointage', 'Salarié · date · heure d’entrée · heure de sortie · motif de correction']],
            ],
            'anomalies' => [
                'title' => 'Anomalies de pointage', 'subtitle' => 'Éléments à vérifier ou à régulariser',
                'description' => 'Repérage des oublis de pointage, retards et incohérences entre planning et heures enregistrées.',
                'stats' => [['À examiner', '08'], ['En cours de régularisation', '03'], ['Clôturées', '42']],
                'columns' => ['Salarié', 'Date', 'Anomalie', 'Détail', 'État'],
                'rows' => [['EMP-014 · T. Rasoanaivo', '03/11/2026', 'Sortie manquante', 'Aucun pointage de fin de journée', 'À examiner'], ['EMP-027 · L. Ranaivo', '02/11/2026', 'Retard', 'Arrivée 24 min après l’horaire', 'À examiner']],
            ],
            'temps-travaille' => [
                'title' => 'Temps réellement travaillé', 'subtitle' => 'Synthèse des heures par période',
                'description' => 'Récapitulatif des heures prévues, travaillées, absences et heures supplémentaires.',
                'stats' => [['Heures prévues', '160 h'], ['Heures pointées', '154 h 30'], ['Écart', '−5 h 30']],
                'columns' => ['Salarié', 'Période', 'Prévu', 'Travaillé', 'Absence', 'Supplémentaire'],
                'rows' => [['Rado Rakoto', 'Octobre 2026', '160 h', '158 h 12', '0 h', '2 h 12'], ['Miora Andrian', 'Octobre 2026', '152 h', '146 h 30', '5 h 30', '0 h']],
                'notice' => 'Les interfaces de transmission vers les absences et les variables de paie sont à définir avec les blocs concernés.',
            ],
        ];
    }

    public function screen(string $slug): ?array
    {
        return $this->screens()[$slug] ?? null;
    }
}
