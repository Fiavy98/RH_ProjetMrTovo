<?php

namespace App\Services\Bloc5;

class AuditService
{
    public function page(): array
    {
        return [
            'slug' => 'audit',
            'title' => 'Confidentialité et audit',
            'subtitle' => 'Habilitations et traçabilité des consultations',
            'description' => 'Espace de conception pour les permissions d’accès aux données sensibles et le journal des opérations.',
            'stats' => [
                ['Événements récents', '24'],
                ['Accès sensibles', '08'],
                ['Règles d’accès', 'À définir'],
            ],
            'columns' => ['Date et heure', 'Utilisateur', 'Action', 'Dossier concerné', 'Résultat'],
            'rows' => [
                ['03/11/2026 09:14', 'RH Démo', 'Consultation du dossier', 'EMP-001', 'Autorisé'],
                ['03/11/2026 08:52', 'Manager Démo', 'Consultation du salaire', 'EMP-002', 'À contrôler'],
            ],
            'notice' => 'Les habilitations, les catégories de données confidentielles et la durée de conservation restent à cadrer avant tout branchement aux données réelles.',
        ];
    }
}
