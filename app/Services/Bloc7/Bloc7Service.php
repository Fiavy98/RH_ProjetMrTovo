<?php

namespace App\Services\Bloc7;

/** Catalogue de maquettes du Bloc 7. Aucune donnée n'est lue ou sauvegardée. */
class Bloc7Service
{
    public function screens(): array
    {
        return [
            'demandes' => [
                'title' => 'Demandes de congé', 'subtitle' => 'Saisie et suivi des demandes',
                'description' => 'Parcours prévu : demande du salarié, contrôle du solde, puis validation Manager/RH.',
                'stats' => [['Mes demandes', '04'], ['En attente', '02'], ['Jours disponibles', '18']],
                'columns' => ['Référence', 'Type de congé', 'Période', 'Durée', 'Statut'],
                'rows' => [['CON-2026-014', 'Congé annuel', '16–20 nov. 2026', '5 jours', 'En attente Manager'], ['CON-2026-009', 'Congé annuel', '05–07 oct. 2026', '3 jours', 'Approuvée']],
                'fields' => [['Nouvelle demande', 'Type de congé · dates de début et fin · motif · justificatif éventuel']],
            ],
            'validations' => [
                'title' => 'Validation des demandes', 'subtitle' => 'Décisions Manager et RH',
                'description' => 'File de demandes à examiner, avec disponibilité, chevauchements et historique des avis.',
                'stats' => [['À valider par Manager', '06'], ['À valider par RH', '03'], ['À corriger', '02']],
                'columns' => ['Salarié', 'Type', 'Période', 'Solde après demande', 'Étape', 'Décision'],
                'rows' => [['Rado Rakoto', 'Congé annuel', '16–20 nov. 2026', '13 jours', 'Manager', 'En attente'], ['Miora Andrian', 'Congé exceptionnel', '12 nov. 2026', '—', 'RH', 'En attente']],
                'notice' => 'La vérification du solde et des chevauchements doit précéder la décision. Les règles de délégation, l’ordre des validations et les droits de décision restent à confirmer avec les rôles du SIRH.',
            ],
            'soldes' => [
                'title' => 'Droits et soldes', 'subtitle' => 'Droits acquis, consommés et disponibles',
                'description' => 'Consultation des droits et soldes de congés par salarié et par type.',
                'stats' => [['Soldes à jour', '121'], ['À régulariser', '07'], ['Période de référence', '2026']],
                'columns' => ['Salarié', 'Type de congé', 'Droit acquis', 'Pris', 'En attente', 'Disponible'],
                'rows' => [['Rado Rakoto', 'Congé annuel', '30 j', '9 j', '5 j', '16 j'], ['Miora Andrian', 'Congé annuel', '30 j', '12 j', '0 j', '18 j']],
                'fields' => [['Droits', 'Salarié · type de congé · période · quantité attribuée']],
            ],
            'absences' => [
                'title' => 'Absences et justificatifs', 'subtitle' => 'Registre des absences et pièces associées',
                'description' => 'Suivi des absences planifiées ou constatées, avec rattachement des justificatifs.',
                'stats' => [['Absences ce mois', '14'], ['Justificatifs à vérifier', '04'], ['Jours d’absence', '27']],
                'columns' => ['Salarié', 'Type', 'Période', 'Justificatif', 'État'],
                'rows' => [['EMP-031 · H. Randria', 'Maladie', '02–03 nov. 2026', 'Certificat médical', 'À vérifier'], ['EMP-054 · S. Rakoto', 'Congé autorisé', '26–27 oct. 2026', '—', 'Enregistrée']],
                'notice' => 'Les absences validées devront mettre à jour le planning et transmettre les variables nécessaires à la paie.',
            ],
            'types-conges' => [
                'title' => 'Types de congé', 'subtitle' => 'Paramétrage des catégories de congé',
                'description' => 'Référentiel des types, règles d’acquisition, pièces requises et paramètres de décompte.',
                'stats' => [['Types actifs', '06'], ['Avec justificatif requis', '02'], ['Règles à compléter', '03']],
                'columns' => ['Type', 'Acquisition', 'Justificatif', 'Décompte jours fériés', 'État'],
                'rows' => [['Congé annuel', '2,5 jours / mois', 'Non', 'À définir', 'Actif'], ['Congé maladie', 'Selon règle RH', 'Oui', 'À définir', 'Actif']],
                'fields' => [['Nouveau type', 'Libellé · description · mode d’acquisition · justificatif requis']],
            ],
        ];
    }

    public function screen(string $slug): ?array
    {
        return $this->screens()[$slug] ?? null;
    }
}
