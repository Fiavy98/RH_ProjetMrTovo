<?php

namespace App\Services\Bloc4;

use App\Services\BlocPageService;

/** Écrans de démonstration du Bloc 4 : contrats, affectations et onboarding. */
class Bloc4PageService extends BlocPageService
{
    public function screens(): array
    {
        return $this->defineScreens([
            'dashboard' => 'Suivi des contrats et intégrations', 'contracts' => 'Liste des contrats',
            'contract-form' => 'Nouveau contrat', 'contract-detail' => 'Fiche contrat',
            'renewals' => 'Renouvellements', 'assignment-b4' => 'Gestion des affectations',
            'documents' => 'Documents', 'document-form' => 'Ajouter un document',
            'onboardings' => 'Liste des onboardings', 'onboarding-detail' => 'Suivi des 6 tâches',
            'contract-settings' => 'Paramètres des contrats',
        ], 'Bloc 4 · Contrats et onboarding');
    }
}
