<?php

namespace App\Services\Bloc5;

/** Catalogue de maquettes du Bloc 5. Aucune donnée n'est lue ou sauvegardée. */
class Bloc5Service
{
    public function screens(): array
    {
        return [
            'dossiers' => [
                'title' => 'Répertoire des salariés', 'subtitle' => 'Référentiel central des dossiers salariés',
                'description' => 'Recherche et accès aux dossiers administratifs.',
                'stats' => [['Salariés actifs', '128'], ['Dossiers à compléter', '07'], ['Documents à renouveler', '12']],
                'columns' => ['Matricule', 'Salarié', 'Département', 'Poste', 'Statut'],
                'rows' => [['EMP-001', 'Rado Rakoto', 'Administration', 'Responsable RH', 'Actif'], ['EMP-002', 'Miora Andrian', 'Finance', 'Comptable', 'Actif']],
            ],
            'dossier' => [
                'title' => 'Dossier administratif', 'subtitle' => 'Identité, coordonnées et situation professionnelle',
                'description' => 'Vue synthétique d’un dossier salarié, avec ses contrats, affectations, salaire et historique de carrière.',
                'stats' => [['Contrat', 'CDI'], ['Ancienneté', '3 ans'], ['Affectation', 'Administration']],
                'fields' => [['Identité', 'Nom, prénom, date de naissance, matricule'], ['Coordonnées', 'Adresse, téléphone, e-mail'], ['Situation professionnelle', 'Département, poste, responsable, date d’embauche'], ['Rémunération', 'Salaire de référence · accès restreint']],
                'columns' => ['Événement de carrière', 'Poste / situation', 'Date d’effet'],
                'rows' => [['Embauche', 'Assistant administratif', '01/03/2023'], ['Changement de poste', 'Responsable RH', '01/06/2025']],
            ],
            'documents' => [
                'title' => 'Documents administratifs', 'subtitle' => 'Pièces du dossier et suivi de validité',
                'description' => 'Vue maquette pour classer les documents, repérer leur état et anticiper leur date d’expiration.',
                'stats' => [['Documents enregistrés', '342'], ['À renouveler', '12'], ['Expirés', '03']],
                'columns' => ['Document', 'Salarié', 'Date d’émission', 'Valide jusqu’au', 'État'],
                'rows' => [['Pièce d’identité', 'Rado Rakoto', '12/04/2024', '12/04/2029', 'Valide'], ['Certificat médical', 'Miora Andrian', '01/05/2025', '01/05/2026', 'À renouveler']],
                'fields' => [['Ajouter un document', 'Salarié · type · fichier · date d’expiration']],
            ],
            'carriere' => [
                'title' => 'Carrière et rémunération', 'subtitle' => 'Contrats, affectations, salaire et historique',
                'description' => 'Vue chronologique des changements professionnels et de rémunération.',
                'stats' => [['Contrats en cours', '116'], ['Affectations actives', '128'], ['Évolutions cette année', '09']],
                'columns' => ['Salarié', 'Événement', 'Poste / contrat', 'Salaire de référence', 'Date d’effet'],
                'rows' => [['Rado Rakoto', 'Promotion', 'Responsable RH · CDI', 'Confidentiel', '01/06/2025'], ['Miora Andrian', 'Renouvellement', 'Comptable · CDD', 'Confidentiel', '01/01/2026']],
            ],
            'audit' => [
                'title' => 'Confidentialité et audit', 'subtitle' => 'Habilitations et traçabilité des consultations',
                'description' => 'Espace de conception pour les permissions d’accès aux données sensibles et le journal des opérations.',
                'stats' => [['Événements récents', '24'], ['Accès sensibles', '08'], ['Règles d’accès', 'À définir']],
                'columns' => ['Date et heure', 'Utilisateur', 'Action', 'Dossier concerné', 'Résultat'],
                'rows' => [['03/11/2026 09:14', 'RH Démo', 'Consultation du dossier', 'EMP-001', 'Autorisé'], ['03/11/2026 08:52', 'Manager Démo', 'Consultation du salaire', 'EMP-002', 'À contrôler']],
                'notice' => 'Les habilitations, les catégories de données confidentielles et la durée de conservation restent à cadrer avant tout branchement aux données réelles.',
            ],
        ];
    }

    public function screen(string $slug): ?array
    {
        return $this->screens()[$slug] ?? null;
    }
}
